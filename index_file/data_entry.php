<div class="">
  <!-- Entry Form -->
  <form class="row g-3 mb-4" method="POST" action="core_file/add_entry.php">

      <!-- Hidden Query Parameters -->
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($query_string) ?>">

    <div class="col-md-2">
        <label class="form-label">তারিখ দিন</label>
        <input type="date" name="date" id="trans_date" placeholder="তারিখ" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">খরচের বিবরণ</label>
        <input type="text" name="description" placeholder="সংক্ষিপ্ত বিবরণ দিন" class="form-control" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">পরিমাণ (৳)</label>
        <input type="number" name="amount" step="0.01" placeholder="টাকার পরিমাণ" class="form-control" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">নির্বাচন করুন</label>
        <select name="category" class="form-select" required>

          <option value="" disabled selected>ক্যাটাগরি দিন</option>
          <optgroup label="দৈনন্দিন খরচ">
            <option value="বাজার">বাজার</option>
            <option value="বাহিরেরখরচ">বাহিরের খরচ</option>
            <option value="মোবাইলখরচ">মোবাইল খরচ</option>
            <option value="গাড়িভাড়া">গাড়ি ভাড়া</option>
            <option value="ঘোরাঘুরি">ঘোরাঘুরি</option>
            <option value="কেনাকাটা">কেনাকাটা</option>
          </optgroup>
          
          <optgroup label="বাড়ি সংক্রান্ত">
            <option value="বাসাভাড়া">বাসা ভাড়া</option>
            <option value="গৃহস্থালীজিনিসপত্র">গৃহস্থালী জিনিসপত্র</option>
            <option value="গৃহস্থালীমেরামত">গৃহস্থালী মেরামত</option>
          </optgroup>

          <optgroup label="ব্যক্তিগত">
            <option value="মালজিনিস">মাল জিনিস</option>
            <option value="কসমেটিক্স">কসমেটিক্স</option>
            <option value="দাওয়াতখরচ">দাওয়াতখরচ</option>
            <option value="বইখাতা">বইখাতা</option>
            <option value="ঔষধ">ঔষধ</option>
            <option value="পরিবার">পরিবার</option>
            <option value="সাইকেলমেরামত">সাইকেল মেরামত</option>
          </optgroup>

          <optgroup label="আর্থিক">
            <option value="প্রাপ্তি">প্রাপ্তি</option>
            <option value="প্রদান">প্রদান</option>
            <option value="আয়">আয়</option>
          </optgroup>

          <option value="অন্যান্য">অন্যান্য</option>
        </select>

    </div>

    <div class="col-md-2">
        <label class="form-label"> ক্লিক করুন</label>
        <button type="submit" class="form-control btn btn-success">যোগ করুন</button>
    </div>
  </form>
</div>