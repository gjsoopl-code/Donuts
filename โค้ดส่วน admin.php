<?php
$file_p = 'products.json';
$file_o = 'orders.json';

// ตรวจสอบไฟล์
if (!file_exists($file_p)) file_put_contents($file_p, json_encode([]));
if (!file_exists($file_o)) file_put_contents($file_o, json_encode([]));

$products = json_decode(file_get_contents($file_p), true);
$orders = json_decode(file_get_contents($file_o), true);

// --- ระบบจัดการสินค้า ---
if (isset($_POST['add_product'])) {
    $products[] = [
        "id" => time(),
        "name" => $_POST['n'],
        "price" => (int)$_POST['p'],
        "stock" => (int)$_POST['s'],
        "image" => $_POST['i']
    ];
    file_put_contents($file_p, json_encode(array_values($products), JSON_PRETTY_PRINT));
    header("location: admin.php");
}

if (isset($_GET['del_product'])) {
    $id = $_GET['del_product'];
    $products = array_filter($products, function($p) use ($id) { return $p['id'] != $id; });
    file_put_contents($file_p, json_encode(array_values($products), JSON_PRETTY_PRINT));
    header("location: admin.php");
}

// --- ระบบจัดการ Log ---
if (isset($_GET['del_log'])) {
    $log_index = $_GET['del_log'];
    unset($orders[$log_index]);
    file_put_contents($file_o, json_encode(array_values($orders), JSON_PRETTY_PRINT));
    header("location: admin.php");
}

if (isset($_GET['clear_all_logs'])) {
    file_put_contents($file_o, json_encode([]));
    header("location: admin.php");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakery Admin - Full Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }
        .admin-header { background: #343a40; color: white; padding: 20px; margin-bottom: 30px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .img-preview { width: 40px; height: 40px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>

<div class="admin-header text-center shadow">
    <h2>⚙️ ระบบจัดการร้านเบเกอรี่</h2>
    <a href="index.php" class="btn btn-outline-light btn-sm mt-2">ไปหน้าหน้าร้าน</a>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card p-4">
                <h5 class="fw-bold mb-3 text-primary">➕ เพิ่มสินค้าใหม่</h5>
                <form method="POST">
                    <div class="mb-2">
                        <label class="small">ชื่อสินค้า</label>
                        <input type="text" name="n" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="small">ราคา (฿)</label>
                            <input type="number" name="p" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="small">สต็อก (ชิ้น)</label>
                            <input type="number" name="s" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small">URL รูปภาพ</label>
                        <input type="url" name="i" class="form-control" placeholder="https://..." required>
                    </div>
                    <button name="add_product" class="btn btn-primary w-100">บันทึกสินค้า</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card p-4">
                <h5 class="fw-bold mb-3">📦 สต็อกและรายการสินค้า</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รูป</th>
                                <th>ชื่อ</th>
                                <th>ราคา</th>
                                <th>สต็อก</th>
                                <th>ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td><img src="<?= $p['image'] ?>" class="img-preview"></td>
                                <td><strong><?= $p['name'] ?></strong></td>
                                <td><?= $p['price'] ?> ฿</td>
                                <td><span class="badge bg-info"><?= $p['stock'] ?></span></td>
                                <td>
                                    <a href="?del_product=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ลบสินค้านี้ใช่ไหม?')">ลบ</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 mt-4">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-danger mb-0">📜 ประวัติการสั่งซื้อ (Log)</h5>
                    <a href="?clear_all_logs=1" class="btn btn-outline-danger btn-sm" onclick="return confirm('ลบ Log ทั้งหมดใช่ไหม?')">ล้าง Log ทั้งหมด</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>วันที่</th>
                                <th>รายการสินค้า</th>
                                <th>ที่อยู่</th>
                                <th>ยอดรวม</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $index => $o): ?>
                            <tr>
                                <td class="small text-muted"><?= $o['date'] ?></td>
                                <td class="small"><?= $o['items'] ?></td>
                                <td class="small"><?= htmlspecialchars($o['address']) ?></td>
                                <td class="fw-bold text-danger"><?= $o['total'] ?> ฿</td>
                                <td>
                                    <a href="?del_log=<?= $index ?>" class="btn btn-link btn-sm text-danger text-decoration-none" onclick="return confirm('ลบรายการนี้ใช่ไหม?')">ลบ</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
