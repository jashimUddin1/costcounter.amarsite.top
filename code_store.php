1. top header 📊 ড্যাশবোর্ড - সেপ্টেম্বর ২০২৫ ar jaygay jsut jodi pc ba laptop user hoy tobe date filter system ta majhe
thakbe.
ex. => left => 📊 ড্যাশবোর্ড - সেপ্টেম্বর ২০২৫ =>Middle=> selection system <= right=> back to home .
    2. selection system er vitore year, month, view (graph/table) ase but ==> year, all and enter button. airokom chai .
    year = year and all = month and all (mani kono month select na hole all month dekhabe ata dropdown a thaka chai)
    3. age default view graph chilo but ekhon default view graph and simple list duitai hobe.
    4. body view = month name center a then 📅 প্রতিদিনের খরচ graph tar niche 📅 প্রতিদিনের তালিকা (12) simple list, and
    dan pashe thakbe 🧾 ক্যাটেগরি ভিত্তিক খরচ grap tar niche 📋 ক্যাটেগরি ভিত্তিক তালিকা (13) simple list.
    5. graph ar niche simple list and simple list ar niche monthly summary thakbe ...


    ar poriborte chai ==>
    single
    Manual
    multiple

    ar notun kore chai multi_entry_one_page


    <!-- ➕ Entry Mode -->
    <div class="mb-4">
        <h6>🧾 Entry Mode</h6>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="entry_mode" id="singleEntry" value="single"
                <?= empty($_SESSION['multi_entry_enabled']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="singleEntry">Single</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="entry_mode" id="multiEntry" value="multiple"
                <?= !empty($_SESSION['multi_entry_enabled']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="multiEntry">Multiple</label>
        </div>

        <!-- ✅ Only for Multiple Mode -->
        <div id="multiEntryOptions"
            style="display: <?= !empty($_SESSION['multi_entry_enabled']) ? 'block' : 'none' ?>; margin-left: 1rem;">

            <div class="form-check">
                <input class="form-check-input" type="radio" name="entry_type_select[]" value="single_date"
                    <?= in_array('single_date', $_SESSION['entry_type_select'] ?? []) ? 'checked' : '' ?>>
                <label class="form-check-label">Single Date Multiple Entry</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="entry_type_select[]" value="multi_date"
                    <?= in_array('multi_date', $_SESSION['entry_type_select'] ?? []) ? 'checked' : '' ?>>
                <label class="form-check-label">Multi Date Multiple Entry</label>
            </div>

        </div>
    </div>




    <!-- ✅ Data Entry Form Selector -->
    <?php
    // Default fallback: single entry form
    if (empty($_SESSION['multi_entry_enabled'])) {
        include "index_file/data_entry.php"; // 👉 Single Entry Mode
    }
    // Multiple Entry Mode
    else {
        $entryTypes = $_SESSION['entry_type_select'] ?? [];

        if (in_array('single_date', $entryTypes)) {
            include "index_file/signle_date_multi_entry.php"; // 👉 Single Date Multiple Entry
        } elseif (in_array('multi_date', $entryTypes)) {
            include "index_file/multi_date_multi_entry.php"; // 👉 Multi Date Multiple Entry
        } else {
            // fallback if no valid entry_type selected
            $_SESSION['warning'] = '⚠️ অনুগ্রহ করে Data Entry Options নির্বাচন করুন।';
        }
    }
    ?>

    <?php
    $entry_mode = $_POST['entry_mode'] ?? 'single';
    $_SESSION['entry_mode'] = $entry_mode;
    $_SESSION['entry_type_select'] = ($entry_mode === 'multiple') ? ($_POST['entry_type_select'] ?? []) : [];
    ?>


    ====================== */
    $entry_mode = $_POST['entry_mode'] ?? 'single';
    $_SESSION['entry_mode'] = $entry_mode;

    if ($entry_mode === 'multiple') {
    $_SESSION['entry_type_select'] = $_POST['entry_type_select'] ?? [];
    } else {
    unset($_SESSION['entry_type_select']);
    }



ok amra akhon index_file/multi_entry_one_page.php ata banano 

same as index_file/signle_date_multi_entry.php and index_file/multi_date_multi_entry.php 
ai dui file ar combination hobe eta hobe multi_entry_one_page.php

signle_date_multi_entry.php => entry format -> : খাবার 40+50, ২.ফল 530, বাজার 25 টাকা,
multi_date_multi_entry.php => entry format -> : 2023-09-01 : খাবার 40+50, ২.ফল 530, বাজার 25 টাকা,

tomi ja bolse sob feature add koro and ami o niche jeigula likhsi seigula o add koro  then combind kore akta ready code daw full

multi_entry_one_page.php => entry format -> : 
feature 1 =>
  2023-09-01 : খাবার 40+50, ২.ফল 530, বাজার 25 টাকা,
  বা
  2025-09-01 : 
  খাবার 40+50
  বাজার 25
  ফল 530
  বা
  ১জানুয়ারী ২০২৫: ১.খাবার 40+50, ২.ফল 530, ৩. বাজার 25 টাকা,
   বা
  ১জানুয়ারী ২০২৫:
  ১.খাবার 40+50 
  ২.ফল 530 
  ৩. বাজার 25 টাকা
   বা
  15 July 2025: ১.খাবার 40+50, ২.ফল 530, ৩. বাজার 25 টাকা,

    বা
  15 July 2025:
  ১.খাবার 40+50 
  ২.ফল 530 
  ৩. বাজার 25 টাকা

feature 2 =>                                           
    ১জানুয়ারী ২০২৫ মঙ্গলবার
    ১. খাবার ৫০  টাকা
    ২. বাজার ৩০ টাকা
    ৩.  টাকা
    ৪.
    ৫. 
    ব্যয়: ৮০ টাকা মোটব্যয়: ৮০ টাকা
    বা
    ১জানুয়ারী ২০২৫
    ১. খাবার ৫০  টাকা
    ২. বাজার ৩০ টাকা

feature 3 =>
    ১৫/০৭/২০২৫
    ১. খাবার ৫০  টাকা
    ২. বাজার ৩০ টাকা
    ৩.  টাকা

    or
    15/07/2025
    ১. খাবার ৫০  টাকা
    ২. বাজার ৩০ টাকা
    ৩.  টাকা
    ৪.
    ৫. 
    ব্যয়: ৮০ টাকা মোটব্যয়: ৮০ টাকা

feature 4 => 
    15 July 2025
    ১. খাবার ৫০  টাকা
    ২. বাজার ৩০ টাকা

ai 3 ta feature e thakbe ai file a . 
ami ki tumake  amar 
signle_date_multi_entry.php
multi_date_multi_entry.php 
file duita devo ? 


na jono vabei kaj hosse na .
ami notun kore amar  chahida update kore dissi kichu feature komiye . aiber kintu kore detei hobe .

date (day) //day thakteo pare aber na thaktei o pare .
entry 
entry

atai amar mul foramt 

example #1:  
১জানুয়ারী ২০২৫ মঙ্গলবার
১. খাবার ৫০  টাকা
২. বাজার ৩০ 

example #2: ১জানুয়ারী ২০২৫
১. খাবার ৫০ 
২. বাজার ৩০ টাকা

example #3: dd/mm/yyyy day
১. খাবার ৫০  টাকা
 বাজার ৩০ টাকা

example #4: 01/01/2025 tuesday
 খাবার ৫০  টাকা
২. বাজার ৩০ টাকা

example #5: 01/01/2025
 খাবার ৫০  টাকা
 বাজার ৩০ টাকা

aivaber jeno kaj kore code full update kore daw please.





১জানুয়ারী ২০২৫ মঙ্গলবার
১. খাবার ১০  টাকা
২. বাজার ২০ 

২ জানুয়ারী ২০২৫
১. খাবার ৩০
২. বাজার 4০ টাকা

3জানুয়ারী ২০২৫
১. খাবার ৫০ 
২. বাজার 6০ টাকা

4 january 2025
১. খাবার 70  টাকা
 বাজার 80 টাকা

05/01/2025 tuesday
খাবার 90  টাকা
২.বাজার 10০ টাকা

06/01/2025
খাবার 110  টাকা
বাজার 12০ টাকা

7জানুয়ারী ২০২৫ : খাবার ১০ টাকা, ২. বাজার ২০
8 জানুয়ারী ২০২৫ : খাবার ১০20 টাকা, ২. বাজার ২০1

preview ==> 

📊 Preview
তারিখ	বিবরণ	টাকা
NaN undefined NaN | undefined	খাবার	১০
NaN undefined NaN | undefined	বাজার	২০
NaN undefined NaN | undefined	খাবার	৩০
NaN undefined NaN | undefined	বাজার	৪০
NaN undefined NaN | undefined	খাবার	৫০
NaN undefined NaN | undefined	বাজার	৬০
৪ জানুয়ারি ২০২৫ | শনিবার	খাবার	৭০
৪ জানুয়ারি ২০২৫ | শনিবার	বাজার	৮০
৪ জানুয়ারি ২০২৫ | শনিবার	0	৫
৪ জানুয়ারি ২০২৫ | শনিবার	খাবার	৯০
৪ জানুয়ারি ২০২৫ | শনিবার	বাজার	১০০
৬ জানুয়ারি ২০২৫ | সোমবার	খাবার	১১০
৬ জানুয়ারি ২০২৫ | সোমবার	বাজার	১২০