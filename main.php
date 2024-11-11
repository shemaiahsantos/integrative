<?php
// Include database connection
include 'conx.php'; // Make sure this path is correct
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$loggedInUser = $_SESSION['username'];

// Fetch user information
try {
    $sql = "SELECT regid, username, img FROM sbf WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$loggedInUser]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($user) {
        $_SESSION['uID'] = $user['regid'];
    } else {
        echo "<p>User not found.</p>";
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch products without added_date
$query = "SELECT item_name, price, description, img_path FROM inventory";
$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 240px;
            background-color: #28a745;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            color: #ffffff;
            margin-bottom: 20px;
        }
        .sidebar .nav-link {
            color: #ffffff;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        .sidebar .nav-link:hover {
            background-color: #218838;
        }

        /* Content area */
        .content {
            margin-left: 260px;
            padding: 40px;
        }
        .content h1 {
            color: #28a745;
            font-weight: bold;
        }

        /* Product styles */
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .product-box {
            flex: 1 1 calc(33.333% - 20px); /* Three products per row */
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .product-box img {
            max-width: 100%;
            height: 200px; /* Set a fixed height for the image */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            border-radius: 8px;
        }

        /* Add to Cart button styling */
        .add-to-cart {
            background-color: #28a745; /* Green background */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            margin-top: auto; /* Pushes the button to the bottom of the box */
        }
        .add-to-cart:hover {
            background-color: #218838; /* Darker green on hover */
            transform: scale(1.05); /* Slightly enlarge on hover */
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>User Dashboard</h2>
    <nav class="nav flex-column">
        <a href="user.php" class="nav-link">Home</a>
        <a href="cart.php" class="nav-link">Cart</a>
        <a href="userprof.php" class="nav-link">User Profile</a>
        <button id="logoutButton" class="nav-link" style="background:none; border:none; color:white; cursor:pointer;">Logout</button>
    </nav>
</div>

<div class="content">
    <h1>Welcome to the User Page</h1>
    <p>Hello, <?php echo htmlspecialchars($loggedInUser); ?>!</p>

    <h2>Available Products</h2>
    <div class="product-container">
        <?php foreach ($products as $product): ?>
            <div class="product-box">
                <img src="<?php echo htmlspecialchars($product['img_path'] ?: 'fileImg/default.png'); ?>" alt="Product Image">
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['item_name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p>Price: ₱<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                    <button class="add-to-cart" onclick="addToCart('<?php echo htmlspecialchars($product['item_name']); ?>')">Add to Cart</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function addToCart(itemName) {
        fetch("add_to_cart.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "itemName=" + encodeURIComponent(itemName)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(itemName + " has been added to your cart.");
            } else {
                alert("Failed to add item to cart: " + (data.message || ""));
            }
        })
        .catch(error => console.error("Error:", error));
    }

    document.getElementById("logoutButton").addEventListener("click", function() {
        var confirmLogout = confirm("Are you sure you want to logout?");
        if (confirmLogout) {
            window.location.href = "index.php";
        }
    });
</script>
</body>
</html>
