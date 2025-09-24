<!-- index_file/multi_entry_one_page.php -->
<div class="card p-3 mb-4">
  <h5 class="mb-3">🧾 Multi Entry (One Page)</h5>

  <form method="POST" action="core_file/multi_entry_one_page_core.php">
    <input type="hidden" name="redirect_query" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">

    <div class="mb-3">
      <label class="form-label">সব এন্ট্রি এক textarea তে লিখুন</label>
      <textarea id="bulkInput" name="bulk_description" class="form-control" rows="14"
        placeholder="👉 উদাহরণ:

2025-09-01 : খাবার 40+50, ফল 530, বাজার 25 টাকা

অথবা

১ জুলাই ২০২৫ মঙ্গলবার
১. খাবার ৫০ টাকা
২. বাজার ৩০ টাকা"></textarea>
    </div>

    <!-- Preview Section -->
    <div class="mt-4">
      <div class="d-flex justify-content-between align-items-center">
        <h6>📊 Preview</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="togglePreview">👁 Preview</button>
      </div>
      <div id="previewWrapper" style="display:none;">
        <table class="table table-bordered table-sm mt-2" id="previewTable">
          <thead>
            <tr>
              <th>তারিখ</th>
              <th>বিবরণ</th>
              <th>টাকা</th>
            </tr>
          </thead>
          <tbody id="previewBody">
            <tr>
              <td colspan="3" id="emptyRow" class="text-muted" style="cursor:pointer;">⌛ কিছু লিখুন...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="text-center mt-3">
      <button type="submit" class="btn btn-success">✅ সবগুলো যুক্ত করুন</button>
    </div>
  </form>
</div>

<script>
// 🔢 বাংলা ↔ ইংরেজি সংখ্যা
function bn2en(str){
  const map={'০':'0','১':'1','২':'2','৩':'3','৪':'4','৫':'5','৬':'6','৭':'7','৮':'8','৯':'9'};
  return str.replace(/[০-৯]/g,d=>map[d]);
}
function en2bn(str){
  const map={'0':'০','1':'১','2':'২','3':'৩','4':'৪','5':'৫','6':'৬','7':'৭','8':'৮','9':'৯'};
  return String(str).replace(/[0-9]/g,d=>map[d]);
}

// 📅 বাংলা মাস ও দিন
const bnMonths=['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
const enMonths=['January','February','March','April','May','June','July','August','September','October','November','December'];
const bnDays=['রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার','শনিবার'];

// 🧾 Parser
function parseEntries(text){
  let lines=text.split("\n");
  let results=[];
  let currentDate=null;

  lines.forEach(line=>{
    line=line.trim();
    if(!line) return;

    // Format 1: YYYY-MM-DD : desc
    let m=line.match(/^(\d{4}-\d{2}-\d{2})\s*:?\s*(.+)?$/);
    if(m){
      currentDate=m[1];
      if(m[2]){
        let entries=m[2].split(/[,\n]/);
        entries.forEach(e=>processEntry(e,currentDate,results));
      }
      return;
    }

    // Format 2: dd/mm/yyyy
    let m2=line.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
    if(m2){
      let d=m2[1].padStart(2,"0"), mth=m2[2].padStart(2,"0"), y=m2[3];
      currentDate=`${y}-${mth}-${d}`;
      return;
    }

    // Format 3: English Month (e.g. 15 July 2025)
    let m3=line.match(/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/);
    if(m3){
      let d=m3[1].padStart(2,"0"), y=m3[3];
      let mth=enMonths.findIndex(mm=>mm.toLowerCase().startsWith(m3[2].toLowerCase()))+1;
      currentDate=`${y}-${String(mth).padStart(2,"0")}-${d}`;
      return;
    }

    // Format 4: বাংলা তারিখ (e.g. ১ জুলাই ২০২৫)
    let bnLine=bn2en(line);
    let m4=bnLine.match(/^(\d{1,2})\s*([^\s\d]+)\s*(\d{4})/u);
    if(m4){
      let d=m4[1].padStart(2,"0"), y=m4[3];
      let mth=bnMonths.findIndex(mm=>mm.includes(m4[2]));
      currentDate=`${y}-${String(mth+1).padStart(2,"0")}-${d}`;
      return;
    }

    // Entry line (with or without serial)
    if(currentDate){
      processEntry(line,currentDate,results);
    }
  });

  return results;
}

// 🔧 Process entry line
function processEntry(entry,date,results){
  if(!entry) return;
  let mm=bn2en(entry.trim());
  mm=mm.replace(/^\d+\.\s*/,""); // serial বাদ
  mm=mm.replace(/টাকা|৳|tk/gi,"");

  // allow space separator
  let parts=mm.match(/(.+?)\s*([\d\+]+)/);
  if(parts){
    let desc=parts[1].trim();
    let amt=parts[2].split("+").reduce((a,b)=>a+parseFloat(b||0),0);
    results.push({date,desc,amt});
  }
}

// 📊 Render Preview
function renderPreview(data){
  let tbody=document.getElementById("previewBody");
  tbody.innerHTML="";
  if(data.length===0){
    tbody.innerHTML='<tr><td colspan="3" id="emptyRow" class="text-muted" style="cursor:pointer;">⌛ কিছু লিখুন...</td></tr>';
    attachEmptyRowClick();
    return;
  }
  data.forEach(r=>{
    let d=new Date(r.date);
    let bnDate=`${en2bn(d.getDate())} ${bnMonths[d.getMonth()]} ${en2bn(d.getFullYear())} | ${bnDays[d.getDay()]}`;
    tbody.innerHTML+=`<tr>
      <td>${bnDate}</td>
      <td>${r.desc}</td>
      <td>${en2bn(r.amt)}</td>
    </tr>`;
  });
}

// ✍️ EmptyRow Click Handler
function attachEmptyRowClick(){
  let empty=document.getElementById("emptyRow");
  if(empty){
    empty.addEventListener("click",()=>{
      document.getElementById("bulkInput").focus();
      empty.innerHTML="✍️ এখানে নয়, উপরের বক্সে লিখুন!";
      setTimeout(()=>{ empty.innerHTML="⌛ কিছু লিখুন..."; },2000);
    });
  }
}
attachEmptyRowClick();

// 🖊 Live Input Handler
document.getElementById("bulkInput").addEventListener("input",function(){
  let data=parseEntries(this.value);
  renderPreview(data);
});

// 👁 Preview Toggle
document.getElementById("togglePreview").addEventListener("click",function(){
  let wrap=document.getElementById("previewWrapper");
  wrap.style.display=(wrap.style.display==="none")?"block":"none";
});
</script>
