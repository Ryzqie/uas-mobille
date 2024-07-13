<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

function readProduct($conn) {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM products WHERE id=$id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Product not found"]);
        }
    } else {
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
    }
}

switch ($method) {
    case 'GET':
        readProduct($conn);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'];
        $price = $data['price'];

        $sql = "INSERT INTO products (name, price) VALUES ('$name', $price)";
        if ($conn->query($sql) === TRUE) {
            $id = $conn->insert_id;
            echo json_encode(["id" => $id, "name" => $name, "price" => $price]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error: " . $conn->error]);
        }
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $data = json_decode(file_get_contents("php://input"), true);
            $name = $data['name'];
            $price = $data['price'];

            $sql = "UPDATE products SET name='$name', price=$price WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["id" => $id, "name" => $name, "price" => $price]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error: " . $conn->error]);
            }
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            $sql = "DELETE FROM products WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["message" => "Product deleted successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error: " . $conn->error]);
            }
        }
        break;

    case 'OPTIONS':
        // Handle preflight requests for CORS
        http_response_code(200);
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

$conn->close();
?>
