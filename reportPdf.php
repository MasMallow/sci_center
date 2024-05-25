<form action="generate_pdf.php" method="GET">
    <div class="form-group">
        <label for="start_date">วันที่เริ่มต้น:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="end_date">วันที่สิ้นสุด:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" required>
    </div>
    <div class="form-group">
        <label for="user_id">User ID:</label>
        <input type="text" id="user_id" name="user_id" value="<?php echo isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id']) : ''; ?>">
    </div>
    <button type="submit" class="btn btn-danger">สร้างรายงาน PDF</button>
</form>
