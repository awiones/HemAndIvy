<?php
require_once __DIR__ . '/../config/config.php';
global $pdo;

// Fetch all categories from the categories table
$catStmt = $pdo->query("
    SELECT c.name AS category, c.id,
        (SELECT COUNT(*) FROM auctions a WHERE a.category = c.name) AS count
    FROM categories c
    ORDER BY c.name ASC
");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// For each category, get a random image from an auction in that category
$categoryImages = [];
foreach ($categories as $cat) {
    $catName = $cat['category'];
    $imgStmt = $pdo->prepare("SELECT image FROM auctions WHERE category = ? AND image IS NOT NULL AND image != '' ORDER BY RAND() LIMIT 1");
    $imgStmt->execute([$catName]);
    $imgRow = $imgStmt->fetch(PDO::FETCH_ASSOC);
    $categoryImages[$catName] = $imgRow ? $imgRow['image'] : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Categories - Hem & Ivy</title>
    <link rel="stylesheet" href="/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">All Categories</h2>
            <p class="section-subtitle">
                Explore all our curated collections by style, era, and occasion.
            </p>
        </div>
        <div class="categories-grid">
            <?php if (empty($categories)): ?>
                <p style="grid-column: 1/-1; text-align:center;">No categories available.</p>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="category-card">
                        <?php
                            $catName = $cat['category'];
                            $imgPath = $categoryImages[$catName] ?? null;
                            if ($imgPath) {
                                $imgPath = preg_replace('#/+#','/',$imgPath);
                                $imgFile = null;
                                if (strpos($imgPath, '/uploads/') === 0) {
                                    $imgFile = realpath(__DIR__ . '/../public' . $imgPath);
                                } else {
                                    $imgFile = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
                                }
                                if (!$imgFile || !file_exists($imgFile)) {
                                    $imgPath = null;
                                }
                            }
                            // Fallback image if no product image found
                            if (!$imgPath) {
                                $imgMap = [
                                    'Vintage' => 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?ixlib=rb-4.0.3&auto=format&fit=crop&w=735&q=80',
                                    'Designer' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80',
                                    'Accessories' => 'https://images.unsplash.com/photo-1617019114583-affb34d1b3cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80',
                                    "Men's" => 'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?ixlib=rb-4.0.3&auto=format&fit=crop&w=711&q=80',
                                    'Luxury' => 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80',
                                    'Sustainable' => 'https://images.unsplash.com/photo-1551232864-3f0890e580d9?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80',
                                ];
                                $imgPath = $imgMap[$catName] ?? 'https://images.unsplash.com/photo-1469398715555-76331a6c7c9b?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80';
                            }
                        ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($catName) ?>">
                        <div class="category-overlay">
                            <h3 class="category-name"><?= htmlspecialchars($catName) ?></h3>
                            <div class="category-count"><?= (int)$cat['count'] ?> item<?= $cat['count'] == 1 ? '' : 's' ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
</body>
</html>
