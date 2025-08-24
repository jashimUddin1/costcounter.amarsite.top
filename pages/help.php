<?php
// help.php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: ../login/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <title>সহায়তা | ডেইলি খরচ এন্ট্রি</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        h2,
        h4 {
            color: #198754;
        }

        code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .example-box {
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .section-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 25px;
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="container py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
            <h2 class="mb-0">📖 সহায়িকা / Help</h2>
            <a href="../index.php" class="btn btn-outline-primary btn-sm">🏠 হোম</a>
        </div>

        <!-- Version Info -->
        <div class="alert alert-info">
            <strong>বর্তমান ভার্সন:</strong> <span class="badge bg-success">v0.05</span>
            <strong class="ms-3">আসছে:</strong> <span class="badge bg-warning">v0.07</span>
        </div>

        <!-- Data Insert Process -->
        <div class="section-card">
            <h4>💾 Multi Entry => Single Date Multi Entry</h4>
            <p></p>
            <ol>
                <li>📅 <strong>তারিখ প্রসেসিং:</strong> তারিখ থেকে <code>year</code>, <code>month</code>, <code>day_name</code> বের করা হয়।</li>
                <li>🔢 <strong>বাংলা সংখ্যা রূপান্তর:</strong> <code>bn2en_number()</code> ফাংশন দিয়ে সংখ্যা রূপান্তর হয় (<code>৫০</code> ➜ <code>50</code>)।</li>
                <li>🔍 <strong>সিরিয়াল ও "টাকা" দিলেও কাজ করবে:</strong> যেমন "১. ডিম ২০ টাকা" </li>
                <li>➕ <strong>যোগফল হিসাব:</strong> <code>২০+১০+৩০</code> ➜ মোট যোগফল। যেমন "১. ডিম ২০+১০+৩০ টাকা"</li>
                <li>🏷️ <strong>ক্যাটেগরি ডিটেকশন:</strong> স্বয়ংক্রিয় ক্যাটেগরি সেট।
                    <ul>
                        <li><code>ডিম</code>, <code>মরিচ</code> ➜ বাজার</li>
                        <li><code>রিচার্জ</code> ➜ মোবাইলখরচ</li>
                        <li><code>ঔষধ</code> ➜ চিকিৎসা</li>
                    </ul>
                </li>
            </ol>

            <div class="example-box">
                <h6>উদাহরণ:</h6>
                <p><strong>ইনপুট:</strong> <code>১. ডিম 220 টাকা, ২. মরিচ 50+30 টাকা, ৩. রিচার্জ 100 টাকা</code></p>
                <p><strong>আউটপুট:</strong></p>
                <ol>
                    <li>ডিম ➜ 220 টাকা ➜ ক্যাটেগরি: বাজার</li>
                    <li>মরিচ ➜ 80 টাকা ➜ ক্যাটেগরি: বাজার</li>
                    <li>রিচার্জ ➜ 100 টাকা ➜ ক্যাটেগরি: মোবাইলখরচ</li>
                </ol>
            </div>
        </div>

        <!-- New Features -->
        <div class="section-card">
            <h4>🆕 ভার্সন 0.05 এ নতুন কী রয়েছে?</h4>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">✔️ Settings panel (Edit, Delete, Entry Mode)</li>
                <li class="list-group-item">✔️ Multiple Entry Mode (একই দিনে / একাধিক দিনে)</li>
                <li class="list-group-item">✔️ ড্যাশবোর্ডে গ্রাফ ও তালিকা ভিউ</li>
                <li class="list-group-item">✔️ ক্যাটেগরি অটো-সাজেশন</li>
                <li class="list-group-item">✔️ Graph layout responsive</li>
            </ul>
        </div>

        <!-- Settings Panel -->
        <div class="section-card">
            <h4>🔧 Settings Panel</h4>
            <p>আপনি নিয়ন্ত্রণ করতে পারবেন:</p>
            <ul>
                <li><strong>Edit:</strong> এন্ট্রির পাশে ✏️ বাটন</li>
                <li><strong>Delete:</strong> এন্ট্রির পাশে 🗑️ বাটন</li>
                <li><strong>Entry Mode:</strong> Single / Multiple মোড</li>
            </ul>
        </div>

        <!-- Multiple Entry Mode -->
        <div class="section-card">
            <h4>🧾 Multiple Entry Mode</h4>
            <p>একবারে একাধিক খরচ যুক্ত করার সুবিধা:</p>
            <ul>
                <li><strong>Single Date:</strong> একই তারিখে একাধিক খরচ</li>
                <li><strong>Multi Date:</strong> বিভিন্ন তারিখে খরচ</li>
            </ul>
        </div>

        <!-- Dashboard -->
        <div class="section-card">
            <h4>📊 ড্যাশবোর্ড</h4>
            <ul>
                <li>🧾 ক্যাটেগরি ভিত্তিক গ্রাফ</li>
                <li>📅 দৈনিক খরচ বার গ্রাফ</li>
                <li>📋 সহজ তালিকা ভিউ</li>
                <li>💰 মোট ব্যালেন্স</li>
            </ul>
        </div>

        <!-- Category Auto Detect -->
        <div class="section-card">
            <h4>📁 ক্যাটেগরি অটো-ডিটেকশন</h4>
            <p>কিওয়ার্ড অনুযায়ী ক্যাটেগরি সেট হয়:</p>
            <ul>
                <li><code>ডিম</code>, <code>মরিচ</code>, <code>আলু</code> ➜ বাজার</li>
                <li><code>ঔষধ</code> ➜ চিকিৎসা</li>
                <li><code>রিচার্জ</code> ➜ মোবাইল</li>
            </ul>
        </div>

        <!-- Tips -->
        <div class="section-card">
            <h4>📌 টিপস</h4>
            <ul>
                <li>ড্যাশবোর্ডে ভিউ সিলেক্টরে "গ্রাফ" / "তালিকা"</li>
                <li>Settings Panel দিয়ে Edit/Delete নিয়ন্ত্রণ</li>
                <li>Single মোড = দ্রুত এন্ট্রি, Multiple = বড় তালিকা</li>
            </ul>
        </div>

        <hr>
        <p class="text-center text-muted">Developed with ❤️ by Jasim | ভার্সন: <strong>0.05</strong></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
