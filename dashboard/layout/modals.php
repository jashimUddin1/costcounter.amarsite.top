
<!-- dashboard/layout/modals.php -->

<!-- Axis Full View Modal -->
<div class="modal fade" id="axisFullView" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <?php 
            if ($is_all_year) {
              echo "📅 প্রতিবছরের খরচ  - সব বছর";
            } elseif ($is_all_month) {
              echo "📅 প্রতিমাসের খরচ  - {$year_bn}";
            } else {
              echo "📅 প্রতিদিনের খরচ  - {$month_label} {$year_bn}";
            }
          ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button>
      </div>
      <div class="modal-body">
        <canvas id="axisChartFull" style="min-height:400px"></canvas>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
      </div>
    </div>
  </div>
</div>

<!-- Category Full View Modal -->
<div class="modal fade" id="categoryFullView" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <?php 
            if ($is_all_year) {
              echo "🧾 বছর ভিত্তিক খরচ  - সব বছর";
            } elseif ($is_all_month) {
              echo "🧾 ক্যাটেগরি ভিত্তিক খরচ  - সকল মাস {$year_bn}";
            } else {
              echo "🧾 ক্যাটেগরি ভিত্তিক খরচ  - {$month_label} {$year_bn}";
            }
          ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button>
      </div>
      <div class="modal-body">
        <canvas id="categoryChartFull" style="min-height:400px"></canvas>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ করুন</button>
      </div>
    </div>
  </div>
</div>


<!-- Category Full View Modal -->
<div class="modal fade" id="categoryFullView" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <?php 
            if ($is_all_year) {
              echo "🧾 বছর ভিত্তিক খরচ (Full View) - সব বছর";
            } elseif ($is_all_month) {
              echo "🧾 ক্যাটেগরি ভিত্তিক খরচ (Full View) - সকল মাস {$year_bn}";
            } else {
              echo "🧾 ক্যাটেগরি ভিত্তিক খরচ (Full View) - {$month_label} {$year_bn}";
            }
          ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <canvas id="categoryChartFull" style="min-height:400px"></canvas>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
