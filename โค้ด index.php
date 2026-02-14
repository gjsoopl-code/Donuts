<?php
$file_p = 'products.json';
$file_o = 'orders.json';
$products = json_decode(file_get_contents($file_p), true);

if (isset($_POST['confirm_order'])) {
    $cart_data = json_decode($_POST['cart_json'], true);
    $address = $_POST['address'];
    $orders = json_decode(file_get_contents($file_o), true);
    $total_bill = 0;
    $items_summary = [];

    foreach ($cart_data as $item) {
        foreach ($products as &$p) {
            if ($p['id'] == $item['id'] && $p['stock'] >= $item['qty']) {
                $p['stock'] -= $item['qty']; // ตัดสต็อกตามจำนวนที่ซื้อ
                $total_bill += ($p['price'] * $item['qty']);
                $items_summary[] = $p['name'] . " (" . $item['qty'] . ")";
            }
        }
    }

    $new_order = [
        "date" => date("d/m/Y H:i"),
        "address" => $address,
        "items" => implode(", ", $items_summary),
        "total" => $total_bill
    ];
    
    $orders[] = $new_order;
    file_put_contents($file_p, json_encode($products, JSON_PRETTY_PRINT));
    file_put_contents($file_o, json_encode($orders, JSON_PRETTY_PRINT));
    echo "<script>alert('สั่งซื้อสำเร็จ! ยอดรวม: $total_bill บาท'); sessionStorage.clear(); window.location='index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakery Shop - Multi Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Sarabun', sans-serif; }
        .bakery-header { background: #e74c3c; color: white; padding: 40px 0; text-align: center; border-bottom: 6px solid #c0392b; }
        .card-product { border: none; border-radius: 12px; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .cart-sticky { position: sticky; top: 20px; background: white; border-radius: 12px; padding: 20px; border: 1px solid #eee; }
        .badge-qty { position: absolute; top: 10px; right: 10px; background: #e74c3c; color: white; padding: 5px 10px; border-radius: 20px; }
    </style>
</head>
<body>

<div class="bakery-header">
    <h1 class="fw-bold">🍞 Bakery Shop</h1>
    <p>เลือกสินค้าที่ต้องการ แล้วกรอกที่อยู่เพื่อสั่งซื้อ</p>
</div>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="row g-3">
                <?php foreach($products as $p): ?>
                <div class="col-6 col-md-4">
                    <div class="card card-product p-2 position-relative">
                        <img src="<?= $p['image'] ?>" class="card-img-top rounded" style="height:140px; object-fit:cover;">
                        <div class="card-body px-1">
                            <h6 class="fw-bold mb-1"><?= $p['name'] ?></h6>
                            <p class="text-danger fw-bold mb-1"><?= $p['price'] ?> ฿</p>
                            <p class="text-muted small mb-2">คงเหลือ: <?= $p['stock'] ?></p>
                            <button onclick="addToCart(<?= $p['id'] ?>, '<?= $p['name'] ?>', <?= $p['price'] ?>, <?= $p['stock'] ?>)" 
                                    class="btn btn-success btn-sm w-100 <?= $p['stock'] <= 0 ? 'disabled' : '' ?>">
                                <?= $p['stock'] > 0 ? '+ เพิ่มสินค้า' : 'หมด' ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="cart-sticky shadow-sm">
                <h5 class="fw-bold">🧺 ตะกร้าของคุณ</h5>
                <hr>
                <div id="cart-list" class="mb-3" style="max-height: 200px; overflow-y: auto;">
                    <p class="text-muted small">ยังไม่มีสินค้าในตะกร้า</p>
                </div>
                <div class="d-flex justify-content-between fw-bold mb-3">
                    <span>รวมทั้งสิ้น:</span>
                    <span class="text-danger" id="cart-total">0 ฿</span>
                </div>
                <form method="POST" id="order-form">
                    <input type="hidden" name="cart_json" id="cart_json">
                    <label class="small fw-bold">ที่อยู่จัดส่ง:</label>
                    <textarea name="address" class="form-control mb-3" rows="3" required placeholder="ระบุชื่อและที่อยู่..."></textarea>
                    <button type="submit" name="confirm_order" class="btn btn-danger w-100 fw-bold py-2" onclick="return checkCart()">ยืนยันสั่งซื้อ</button>
                </form>
                <a href="admin.php" class="btn btn-link btn-sm w-100 text-muted mt-2">จัดการสต็อก (Admin)</a>
            </div>
        </div>
    </div>
</div>

<script>
let cart = JSON.parse(sessionStorage.getItem('bakery_cart')) || [];

function addToCart(id, name, price, stock) {
    let item = cart.find(i => i.id === id);
    if (item) {
        if (item.qty < stock) item.qty++;
        else alert('สินค้าในสต็อกไม่พอ');
    } else {
        cart.push({ id, name, price, qty: 1 });
    }
    renderCart();
}

function renderCart() {
    let html = '';
    let total = 0;
    cart.forEach((item, index) => {
        total += item.price * item.qty;
        html += `<div class="d-flex justify-content-between small mb-2">
                    <span>${item.name} x ${item.qty}</span>
                    <span class="text-dark">${item.price * item.qty} ฿ 
                    <button class="btn btn-sm text-danger p-0 ms-2" onclick="removeItem(${index})">✕</button></span>
                 </div>`;
    });
    document.getElementById('cart-list').innerHTML = html || '<p class="text-muted small">ยังไม่มีสินค้าในตะกร้า</p>';
    document.getElementById('cart-total').innerText = total + ' ฿';
    document.getElementById('cart_json').value = JSON.stringify(cart);
    sessionStorage.setItem('bakery_cart', JSON.stringify(cart));
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function checkCart() {
    if (cart.length === 0) { alert('กรุณาเลือกสินค้าก่อนครับ'); return false; }
    return true;
}

renderCart();
</script>
</body>
</html>
