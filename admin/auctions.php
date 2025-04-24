<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
global $pdo;
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

// Fetch distinct categories for dropdown
$categories = [];
$stmtCat = $pdo->query("SELECT DISTINCT category FROM auctions WHERE category IS NOT NULL AND category != ''");
$categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $rarity_num = isset($_POST['rarity_num']) ? intval($_POST['rarity_num']) : 0;
    $rarity_total = isset($_POST['rarity_total']) ? intval($_POST['rarity_total']) : 0;
    $end_time = !empty($_POST['end_time']) ? date('Y-m-d H:i:s', strtotime($_POST['end_time'])) : null;
    $category = trim($_POST['category'] ?? '');
    $new_category = trim($_POST['new_category'] ?? '');
    if ($category === '__new__' && $new_category !== '') {
        $category = $new_category;
    }
    $imagePath = '';

    // Accept cropped images uploaded as base64 data (from JS cropper)
    if (
        isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK
        && $_FILES['image']['size'] > 0
    ) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('auction_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $imagePath = '/uploads/' . $filename;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid image type.';
        }
    }
    // Accept base64 PNG from hidden input (cropper fallback)
    elseif (!empty($_POST['cropped_image_base64'])) {
        $base64 = $_POST['cropped_image_base64'];
        if (preg_match('/^data:image\/png;base64,/', $base64)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $data = base64_decode($base64);
            if ($data !== false) {
                $filename = uniqid('auction_', true) . '.png';
                $uploadDir = __DIR__ . '/../public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $target = $uploadDir . $filename;
                if (file_put_contents($target, $data) !== false) {
                    $imagePath = '/uploads/' . $filename;
                } else {
                    $error = 'Failed to save cropped image.';
                }
            } else {
                $error = 'Invalid cropped image data.';
            }
        } else {
            $error = 'Invalid cropped image format.';
        }
    }
    // If no file uploaded at all
    else {
        $error = 'Image is required.';
    }

    if (!$error && $title && $price > 0 && $description && $imagePath) {
        $rarity = '';
        if ($rarity_num > 0 && $rarity_total > 0) {
            $rarity = "{$rarity_num} of {$rarity_total}";
        }
        $stmt = $pdo->prepare("INSERT INTO auctions (title, price, description, rarity, image, end_time, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $price, $description, $rarity, $imagePath, $end_time, $category])) {
            $success = 'Auction created successfully!';
        } else {
            $error = 'Database error.';
        }
    } elseif (!$error) {
        $error = 'All fields are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Auction</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --imperial-purple: #4B286D;
            --imperial-purple-light: #5d3384;
            --imperial-purple-dark: #3e2159;
            --aged-gold: #C9A050;
            --aged-gold-light: #d4b06c;
            --aged-gold-dark: #b08e46;
            --charcoal-velvet: #3A3A3A;
            --charcoal-light: #4e4e4e;
            --light-gray: #F5F5F5;
            --border-color: #e5e5e5;
            --text-primary: #333333;
            --text-secondary: #6c757d;
            --success-green: #28a745;
            --warning-orange: #fd7e14;
            --danger-red: #dc3545;
            --info-blue: #17a2b8;
            --white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-main {
            flex: 1;
            padding: 0;
            background-color: var(--light-gray);
        }

        .admin-header {
            background-color: var(--imperial-purple);
            color: var(--white);
            padding: 1.5rem 2rem;
            box-shadow: var(--shadow-md);
        }

        .admin-header h1 {
            margin: 0;
            font-size: 2rem;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .dashboard-content {
            padding: 2rem;
        }

        .dashboard-col {
            max-width: 800px;
            margin: 0 auto;
        }

        .admin-form .form-group {
            margin-bottom: 1.5rem;
        }

        .admin-form .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--charcoal-velvet);
        }

        .admin-form .input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .admin-form .input:focus {
            outline: none;
            border-color: var(--imperial-purple-light);
            box-shadow: 0 0 0 3px rgba(75, 40, 109, 0.15);
        }

        .admin-form .input[type="file"] {
            padding: 0.5rem 0;
            border: none;
        }

        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: var(--radius-sm);
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--imperial-purple);
            color: var(--white);
            border-color: var(--imperial-purple);
        }

        .btn-primary:hover {
            background-color: var(--imperial-purple-light);
            border-color: var(--imperial-purple-light);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--imperial-purple);
            border-color: var(--imperial-purple);
        }

        .btn-outline:hover {
            background-color: var(--imperial-purple);
            color: var(--white);
        }

        .alert {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius-sm);
            border-left: 4px solid;
        }

        .alert-error {
            background-color: #ffeaea;
            color: var(--danger-red);
            border-left-color: var(--danger-red);
        }

        .alert-success {
            background-color: #eafaf1;
            color: var(--success-green);
            border-left-color: var(--success-green);
        }

        .actions-container {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-content {
                padding: 1rem;
            }
            
            .actions-container {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-left: 0 !important;
                margin-bottom: 0.5rem;
            }
        }

        /* --- Enhanced Auction Form Styles --- */
        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }
        
        .enhanced-input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .enhanced-input:focus {
            outline: none;
            border-color: var(--imperial-purple);
            box-shadow: 0 0 0 3px rgba(75, 40, 109, 0.15);
            transform: translateY(-1px);
        }
        
        .enhanced-input::placeholder {
            color: #aaa;
            font-style: italic;
        }
        
        .required-star {
            color: var(--danger-red);
            font-weight: bold;
            margin-left: 3px;
        }
        
        .input-help {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        
        .help-text {
            color: var(--text-secondary);
            font-style: italic;
        }
        
        .char-counter {
            color: var(--imperial-purple);
            font-weight: 500;
        }
        
        .price-input-wrapper {
            position: relative;
            max-width: 300px;
            display: flex;
            align-items: center;
        }

        .currency-symbol {
            /* Remove absolute positioning, use flex instead */
            position: static;
            min-width: 32px;
            margin-right: 12px;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 1.08em;
            letter-spacing: 0.05em;
            padding-right: 0;
            /* vertical align handled by flex */
        }

        .price-input-wrapper input {
            padding-left: 0; /* Remove extra left padding */
            flex: 1 1 0%;
        }
        
        /* Validation styles */
        .enhanced-input.is-valid {
            border-color: var(--success-green);
            background-color: rgba(40, 167, 69, 0.05);
        }
        
        .enhanced-input.is-invalid {
            border-color: var(--danger-red);
            background-color: rgba(220, 53, 69, 0.05);
        }
        
        .validation-feedback {
            font-size: 0.85rem;
            margin-top: 0.4rem;
            display: none;
        }
        
        .invalid-feedback {
            color: var(--danger-red);
        }
        
        .valid-feedback {
            color: var(--success-green);
        }
        
        .enhanced-input.is-invalid + .validation-feedback.invalid-feedback,
        .enhanced-input.is-valid + .validation-feedback.valid-feedback {
            display: block;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="admin-main">
        <header class="admin-header">
            <h1>Add New Auction</h1>
        </header>
        <div class="dashboard-content">
            <div class="dashboard-col">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="admin-form">
                    <div class="form-group">
                        <label class="form-label">Image</label>
                        <div id="image-drop-area" style="border:2px dashed var(--imperial-purple);border-radius:var(--radius-md);padding:1.5rem;text-align:center;cursor:pointer;background:#faf7fc;transition:border-color 0.2s;">
                            <span id="drop-text">Drag &amp; drop image here or <span style="color:var(--imperial-purple);text-decoration:underline;cursor:pointer;">browse</span></span>
                            <input type="file" name="image" id="image-input" accept="image/*" class="input" style="display:none;" />
                            <input type="hidden" name="cropped_image_base64" id="cropped-image-base64" value="" />
                            <div id="image-preview" style="margin-top:1rem;display:none;">
                                <img src="" alt="Preview" style="max-width:180px;max-height:180px;border-radius:var(--radius-sm);box-shadow:var(--shadow-sm);border:1px solid var(--border-color);" />
                            </div>
                        </div>
                        <small style="display: block; margin-top: 0.5rem; color: var(--text-secondary);">Supported formats: JPG, JPEG, PNG, GIF, WEBP</small>
                    </div>
                    <!-- Enhanced Title Field -->
                    <div class="form-group">
                        <label class="form-label" for="auction-title">Title <span class="required-star">*</span></label>
                        <input 
                            type="text" 
                            name="title" 
                            id="auction-title" 
                            required 
                            class="input enhanced-input" 
                            placeholder="Enter a descriptive auction title" 
                            maxlength="100"
                            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                        />
                        <div class="input-help">
                            <span class="char-counter"><span id="title-char-count">0</span>/100</span>
                            <span class="help-text">A clear, concise title attracts more bidders</span>
                        </div>
                    </div>
                    <!-- Enhanced Starting Price Field -->
                    <div class="form-group">
                        <label class="form-label" for="auction-price-visible">Starting Price (USD) <span class="required-star">*</span></label>
                        <div class="price-input-wrapper">
                            <span class="currency-symbol">$</span>
                            <input 
                                type="text"
                                id="auction-price-visible"
                                class="input enhanced-input"
                                placeholder="0.00"
                                autocomplete="off"
                                inputmode="decimal"
                                value="<?= isset($_POST['price']) ? number_format(floatval($_POST['price']), 2, '.', ',') : '' ?>"
                            />
                            <input 
                                type="hidden"
                                name="price"
                                id="auction-price"
                                value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                            />
                        </div>
                        <div class="input-help">
                            <span class="help-text">Set a competitive starting price to attract initial bids</span>
                        </div>
                    </div>
                    <!-- Enhanced Description Field -->
                    <div class="form-group">
                        <label class="form-label" for="auction-description">Description <span class="required-star">*</span></label>
                        <textarea 
                            name="description" 
                            id="auction-description" 
                            required 
                            class="input enhanced-input" 
                            placeholder="Provide a detailed description including condition, history, and any unique features" 
                            rows="6"
                            maxlength="2000"
                        ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        <div class="input-help">
                            <span class="char-counter"><span id="desc-char-count">0</span>/2000</span>
                            <span class="help-text">Be thorough - detailed descriptions lead to higher bids</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rarity</label>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="number" name="rarity_num" min="1" class="input" style="width:80px;" placeholder="e.g. 7" />
                            <span>of</span>
                            <input type="number" name="rarity_total" min="1" class="input" style="width:80px;" placeholder="e.g. 7" />
                        </div>
                        <small style="display:block;margin-top:0.5rem;color:var(--text-secondary);">
                            Optional. Example: 7 of 7 &mdash; Only 7 of this product were ever made and will never be produced again.
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End of Auction</label>
                        <input type="datetime-local" name="end_time" class="input" />
                        <small style="display:block;margin-top:0.5rem;color:var(--text-secondary);">
                            Optional. Set when the auction will end. Leave blank for no end date.
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" id="category-select" class="input" style="width:100%;max-width:300px;">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                            <option value="__new__">Add new...</option>
                        </select>
                        <input type="text" name="new_category" id="new-category-input" class="input" style="width:100%;max-width:300px;display:none;margin-top:8px;" placeholder="Enter new category" />
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var select = document.getElementById('category-select');
                        var newInput = document.getElementById('new-category-input');
                        select.addEventListener('change', function() {
                            if (this.value === '__new__') {
                                newInput.style.display = 'block';
                                newInput.required = true;
                            } else {
                                newInput.style.display = 'none';
                                newInput.required = false;
                            }
                        });

                        // Robust JS validation for image input
                        var form = document.querySelector('.admin-form');
                        var imageInput = document.getElementById('image-input');
                        var previewDiv = document.getElementById('image-preview');
                        var previewImg = previewDiv ? previewDiv.querySelector('img') : null;

                        form.addEventListener('submit', function(e) {
                            var hasImageFile = imageInput.files && imageInput.files.length > 0;
                            var hasPreview =
                                previewDiv && previewDiv.style.display !== 'none' &&
                                previewImg && previewImg.src &&
                                previewImg.src.length > 10 &&
                                !/^(about:blank|data:|)$/.test(previewImg.src);

                            if (!hasImageFile && !hasPreview) {
                                alert('Please select and crop an image before submitting.');
                                e.preventDefault();
                                return false;
                            }
                        });
                    });
                    </script>
                    <div class="actions-container">
                        <button type="submit" class="btn btn-primary">Create Auction</button>
                        <a href="/admin/auctions" class="btn btn-outline">Back to Auctions</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

    <script src="/assets/js/cropper.js"></script>
    <!-- Enhanced Auction Form JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character counters
        const titleInput = document.getElementById('auction-title');
        const titleCounter = document.getElementById('title-char-count');
        const descInput = document.getElementById('auction-description');
        const descCounter = document.getElementById('desc-char-count');
        
        function updateCharCount(input, counter) {
            if (input && counter) {
                counter.textContent = input.value.length;
                
                // Change color when approaching limit
                if (input.value.length > (input.maxLength * 0.9)) {
                    counter.style.color = 'var(--danger-red)';
                } else if (input.value.length > (input.maxLength * 0.7)) {
                    counter.style.color = 'var(--warning-orange)';
                } else {
                    counter.style.color = 'var(--imperial-purple)';
                }
            }
        }
        
        if (titleInput && titleCounter) {
            updateCharCount(titleInput, titleCounter);
            titleInput.addEventListener('input', function() {
                updateCharCount(titleInput, titleCounter);
            });
        }
        
        if (descInput && descCounter) {
            updateCharCount(descInput, descCounter);
            descInput.addEventListener('input', function() {
                updateCharCount(descInput, descCounter);
            });
        }
        
        // Price input formatting with thousands separator
        const priceInput = document.getElementById('auction-price');
        const priceVisible = document.getElementById('auction-price-visible');
        if (priceVisible && priceInput) {
            // Format number with commas and dot
            function formatNumber(val) {
                let parts = val.replace(/[^0-9.]/g, '').split('.');
                let intPart = parts[0] || '';
                let decPart = parts[1] || '';
                intPart = intPart.replace(/^0+(?=\d)/, ''); // Remove leading zeros
                let formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                if (decPart.length > 2) decPart = decPart.slice(0,2);
                if (decPart.length > 0) {
                    formatted += '.' + decPart;
                }
                return formatted;
            }
            // Parse formatted string to float string
            function parseNumber(val) {
                let num = val.replace(/,/g, '');
                return num;
            }
            // On input, format and update hidden field
            priceVisible.addEventListener('input', function() {
                let caret = this.selectionStart;
                let before = this.value.length;
                let formatted = formatNumber(this.value);
                this.value = formatted;
                let after = this.value.length;
                this.setSelectionRange(caret + (after - before), caret + (after - before));
                priceInput.value = parseNumber(formatted);
            });
            // On blur, force two decimals
            priceVisible.addEventListener('blur', function() {
                let num = parseFloat(parseNumber(this.value));
                if (!isNaN(num)) {
                    let fixed = num.toFixed(2);
                    this.value = formatNumber(fixed);
                    priceInput.value = fixed;
                } else {
                    this.value = '';
                    priceInput.value = '';
                }
            });
            // On page load, sync hidden and visible
            if (priceInput.value) {
                let num = parseFloat(priceInput.value);
                if (!isNaN(num)) {
                    priceVisible.value = formatNumber(num.toFixed(2));
                }
            }
        }

        // Form validation
        const form = document.querySelector('.admin-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate title
                if (titleInput && titleInput.value.trim().length < 5) {
                    titleInput.classList.add('is-invalid');
                    titleInput.classList.remove('is-valid');
                    isValid = false;
                    
                    // Add validation message if not exists
                    let feedback = titleInput.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('validation-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'validation-feedback invalid-feedback';
                        feedback.textContent = 'Title must be at least 5 characters long';
                        titleInput.parentNode.insertBefore(feedback, titleInput.nextSibling);
                    }
                } else if (titleInput) {
                    titleInput.classList.remove('is-invalid');
                    titleInput.classList.add('is-valid');
                }
                
                // Validate price
                if (priceInput && (isNaN(parseFloat(priceInput.value)) || parseFloat(priceInput.value) <= 0)) {
                    priceInput.classList.add('is-invalid');
                    priceInput.classList.remove('is-valid');
                    isValid = false;
                    
                    // Add validation message if not exists
                    let feedback = priceInput.parentNode.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('validation-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'validation-feedback invalid-feedback';
                        feedback.textContent = 'Please enter a valid price greater than zero';
                        priceInput.parentNode.parentNode.insertBefore(feedback, priceInput.parentNode.nextSibling);
                    }
                } else if (priceInput) {
                    priceInput.classList.remove('is-invalid');
                    priceInput.classList.add('is-valid');
                }
                
                // Validate description
                if (descInput && descInput.value.trim().length < 20) {
                    descInput.classList.add('is-invalid');
                    descInput.classList.remove('is-valid');
                    isValid = false;
                    
                    // Add validation message if not exists
                    let feedback = descInput.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('validation-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'validation-feedback invalid-feedback';
                        feedback.textContent = 'Description must be at least 20 characters long';
                        descInput.parentNode.insertBefore(feedback, descInput.nextSibling);
                    }
                } else if (descInput) {
                    descInput.classList.remove('is-invalid');
                    descInput.classList.add('is-valid');
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
    });
    </script>
</body>
</html>