<?php
$pageTitle = 'Blog CMS — Apollo Admin';
require_once __DIR__ . '/header.php';

$message = '';
$messageType = '';

// Handle Create / Update Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $postId = intval($_POST['post_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower(trim($_POST['slug'] ?? '')));
    $category = trim($_POST['category'] ?? '');
    $status = trim($_POST['status'] ?? 'published');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $slug === '' || $content === '') {
        $message = "Please fill in all required fields (Title, Slug, and Content).";
        $messageType = 'error';
    } else {
        try {
            if ($action === 'create') {
                // Verify slug uniqueness
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS count FROM blog_posts WHERE slug = ?");
                $stmtCheck->execute([$slug]);
                if ($stmtCheck->fetch()['count'] > 0) {
                    throw new Exception("The slug '{$slug}' is already in use by another post.");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO blog_posts (title, slug, summary, content, category, status, published_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$title, $slug, $summary, $content, $category, $status]);
                $message = "New blog post '{$title}' published successfully.";
                $messageType = 'success';
            } elseif ($action === 'update') {
                // Verify slug uniqueness excluding current post
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS count FROM blog_posts WHERE slug = ? AND id != ?");
                $stmtCheck->execute([$slug, $postId]);
                if ($stmtCheck->fetch()['count'] > 0) {
                    throw new Exception("The slug '{$slug}' is already in use by another post.");
                }

                $stmt = $pdo->prepare("
                    UPDATE blog_posts 
                    SET title = ?, slug = ?, summary = ?, content = ?, category = ?, status = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $summary, $content, $category, $status, $postId]);
                $message = "Blog post '{$title}' updated successfully.";
                $messageType = 'success';
            }
            
            // Redirect to prevent form resubmission
            header("Location: blog.php?msg=" . urlencode($message) . "&msg_type=" . urlencode($messageType));
            exit;
        } catch (Exception $e) {
            $message = "Operation failed: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Handle Delete Operation
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$deleteId]);
        $message = "Blog post deleted successfully.";
        $messageType = 'warning';
        header("Location: blog.php?msg=" . urlencode($message) . "&msg_type=" . urlencode($messageType));
        exit;
    } catch (PDOException $e) {
        $message = "Failed to delete blog post: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Check for redirect message
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['msg_type'] ?? 'success';
}

// Fetch single post for editing
$editPost = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmtEdit = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmtEdit->execute([$editId]);
    $editPost = $stmtEdit->fetch();
}

// Fetch all blog posts
try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts ORDER BY published_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('blog.php fetch error — ' . $e->getMessage());
    $posts = [];
    $message = "Failed to load blog posts: " . $e->getMessage();
    $messageType = 'error';
}

$categoriesList = ['Residential', 'Commercial', 'General', 'External', 'NDIS'];
?>

<style>
    .blog-layout {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 1.5rem;
    }
    
    .form-control {
        margin-bottom: 1.25rem;
    }
    .form-control label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .form-control input, 
    .form-control select, 
    .form-control textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        background: #fafafa;
        transition: border-color 0.2s, background 0.2s;
    }
    .form-control input:focus, 
    .form-control select:focus, 
    .form-control textarea:focus {
        border-color: var(--primary);
        background: var(--white);
    }
    .form-control select {
        height: 42px;
    }
    
    .alert-banner {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        font-weight: 600;
    }
    .alert-banner-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-banner-warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .alert-banner-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    @media (max-width: 991px) {
        .blog-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php if ($message): ?>
    <div class="alert-banner alert-banner-<?= htmlspecialchars($messageType) ?>">
        <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="blog-layout">
    <!-- Blog Posts Table -->
    <div class="card">
        <div class="card-title">
            <span>Published Articles</span>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title / Details</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Published Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 3rem;">No blog posts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--primary-dark); display:block;"><?= htmlspecialchars($post['title']) ?></strong>
                                    <span style="font-size:0.75rem;color:var(--text-secondary);">Slug: <code><?= htmlspecialchars($post['slug']) ?></code></span>
                                </td>
                                <td>
                                    <span class="badge" style="background:#e0e7ff;color:#3730a3;">
                                        <?= htmlspecialchars($post['category']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($post['status']) ?>">
                                        <?= htmlspecialchars($post['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('M j, Y, g:i a', strtotime($post['published_at'])) ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-group" style="justify-content: flex-end;">
                                        <a href="blog.php?edit=<?= $post['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Edit Post">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <a href="blog.php?delete=<?= $post['id'] ?>" class="btn-admin btn-admin-danger btn-admin-sm" onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.')" title="Delete Post">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Form -->
    <div class="card">
        <div class="card-title">
            <span><?= $editPost ? 'Edit Blog Post' : 'Create Blog Post' ?></span>
        </div>
        
        <form method="POST" action="blog.php" id="blogForm">
            <input type="hidden" name="action" value="<?= $editPost ? 'update' : 'create' ?>">
            <input type="hidden" name="post_id" value="<?= htmlspecialchars($editPost['id'] ?? 0) ?>">
            
            <div class="form-control">
                <label for="title">Post Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($editPost['title'] ?? '') ?>" required placeholder="e.g. 5 Oven Cleaning Secrets" onkeyup="generateSlug(this.value)">
            </div>

            <div class="form-control">
                <label for="slug">URL Slug</label>
                <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($editPost['slug'] ?? '') ?>" required placeholder="e.g. 5-oven-cleaning-secrets">
            </div>

            <div class="form-control">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?= $cat ?>" <?= (isset($editPost['category']) && $editPost['category'] === $cat) ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-control">
                <label for="status">Publication Status</label>
                <select id="status" name="status" required>
                    <option value="published" <?= (isset($editPost['status']) && $editPost['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= (isset($editPost['status']) && $editPost['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>

            <div class="form-control">
                <label for="summary">Excerpt / Summary</label>
                <textarea id="summary" name="summary" rows="3" placeholder="Brief intro to display on lists..." required><?= htmlspecialchars($editPost['summary'] ?? '') ?></textarea>
            </div>

            <div class="form-control">
                <label for="content">Full Content (HTML tags supported)</label>
                <textarea id="content" name="content" rows="12" placeholder="Write full article here. Use HTML like <p>, <h2>, <ul> etc." required><?= htmlspecialchars($editPost['content'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;gap:0.5rem;margin-top:1.5rem;">
                <button type="submit" class="btn-admin btn-admin-primary" style="flex-grow:1;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Save &amp; Publish
                </button>
                <?php if ($editPost): ?>
                    <a href="blog.php" class="btn-admin btn-admin-outline" style="justify-content:center;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    function generateSlug(text) {
        <?php if ($editPost): ?>
        // Do not auto-overwrite slug on edit unless user edits it manually
        return;
        <?php endif; ?>
        const slug = text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
        document.getElementById('slug').value = slug;
    }
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
