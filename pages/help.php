<?php
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
            padding: 20px;
        }

        h2 {
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
    </style>
</head>

<body class="bg-light">

    <div class="container py-4">

        <!-- <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">📘 সহায়তা কেন্দ্র</h2>
            <a href="../index.php" class="btn btn-outline-success btn-sm">🔙 Back to Home</a>
        </div> -->

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-4">📖 সহায়িকা / Help</h2>
            <a href="../index.php" class="btn btn-outline-primary mb-4">🏠 হোম</a>
        </div>

        <div class="alert alert-info">
            <strong>বর্তমান ভার্সন:</strong> <span class="badge bg-success">v0.05</span>
        </div>

        <h4 class="mt-4">🆕 ভার্সন 0.05 এ নতুন কী রয়েছে?</h4>
        <ul class="list-group mb-4">
            <li class="list-group-item">✔️ Settings panel যোগ হয়েছে: Edit, Delete ও Entry Mode কন্ট্রোল</li>
            <li class="list-group-item">✔️ Multiple Entry Mode চালু হয়েছে (একই দিনে একাধিক এন্ট্রি / একাধিক দিনে একাধিক
                এন্ট্রি)</li>
            <li class="list-group-item">✔️ ড্যাশবোর্ডে গ্রাফিক্যাল ও সহজ তালিকা ভিউ যোগ</li>
            <li class="list-group-item">✔️ ক্যাটেগরি অটো-সাজেশন সিস্টেম (keyword অনুযায়ী অটো category সেট)</li>
            <li class="list-group-item">✔️ Graph layout optimized (responsive & সুন্দরভাবে side by side)</li>
        </ul>

        <h4 class="mt-4">🔧 Settings Panel</h4>
        <p>Settings panel দিয়ে আপনি নিয়ন্ত্রণ করতে পারবেন:</p>
        <ul>
            <li><strong>Edit:</strong> অন করলে এন্ট্রির পাশে ✏️ Edit বাটন দেখা যাবে</li>
            <li><strong>Delete:</strong> অন করলে 🗑️ Delete বাটন দেখা যাবে</li>
            <li><strong>Entry Mode:</strong> Single / Multiple Entry Mode সিলেক্ট করা যাবে</li>
        </ul>

        <h4 class="mt-4">🧾 Multiple Entry Mode</h4>
        <p>এই মোডে আপনি একবারে একাধিক খরচ যুক্ত করতে পারেন:</p>
        <ul>
            <li><strong>Single Date Multiple Entry:</strong> একই তারিখে একাধিক খরচ</li>
            <li><strong>Multi Date Multiple Entry:</strong> বিভিন্ন তারিখে একাধিক খরচ</li>
        </ul>
        <p class="text-muted"><em>যেমন: বাজার, ডিম, মরিচ ইত্যাদি একসাথে একাধিক এন্ট্রি যোগ করা যাবে।</em></p>

        <h4 class="mt-4">📊 ড্যাশবোর্ড</h4>
        <p>ড্যাশবোর্ডে আপনি দেখতে পাবেন:</p>
        <ul>
            <li>🧾 <strong>ক্যাটেগরি ভিত্তিক খরচের গ্রাফ</strong></li>
            <li>📅 <strong>প্রতিদিনের খরচের বার গ্রাফ</strong></li>
            <li>📋 <strong>সহজ তালিকা ভিউ</strong> (তারিখ অনুযায়ী খরচের সারসংক্ষেপ)</li>
            <li>💰 <strong>মোট ব্যালেন্স</strong> প্রদর্শন</li>
        </ul>

        <h4 class="mt-4">📁 ক্যাটেগরি অটো-ডিটেকশন</h4>
        <p>আপনার বর্ণনায় থাকা কীওয়ার্ড অনুযায়ী অটোভাবে ক্যাটেগরি নির্বাচন হয়।</p>
        <p><strong>উদাহরণ:</strong></p>
        <ul>
            <li><code>ডিম</code>, <code>মরিচ</code>, <code>আলু</code> ➜ ক্যাটেগরি: বাজার</li>
            <li><code>ঔষধ</code> ➜ ক্যাটেগরি: চিকিৎসা</li>
            <li><code>রিচার্জ</code> ➜ ক্যাটেগরি: মোবাইল</li>
        </ul>

        <h4 class="mt-4">📌 টিপস</h4>
        <ul>
            <li>ড্যাশবোর্ডে ভিউ সিলেক্টরে "গ্রাফ" ও "সহজ তালিকা" আলাদাভাবে দেখতে পারবেন</li>
            <li>Settings Panel ব্যবহার করে Edit/Delete সহজেই অন/অফ করতে পারবেন</li>
            <li>Single Entry মোড দ্রুত এন্ট্রির জন্য উপযুক্ত, Multiple মোড বড় তালিকার জন্য</li>
        </ul>

        <hr>
        <p class="text-center text-muted">Developed with ❤️ by Jasim | ভার্সন: <strong>0.05</strong></p>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>