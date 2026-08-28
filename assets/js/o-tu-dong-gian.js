// =====================================================================
// o-tu-dong-gian.js - O nhap "tu dong gian" chieu cao theo noi dung.
//
// Cac truong dia chi/ten khach/ghi chu... truoc day la <input> 1 dong nen
// noi dung dai bi cat mat trong khung nho, quan ly/tai xe khong doc duoc
// het neu khong bam vao roi cuon ngang. Doi sang <textarea rows="1"> roi
// tu resize chieu cao theo dung noi dung: ngan thi gon 1 dong nhu input
// binh thuong, dai thi tu xuong dong hien du chu - khong bao gio mat noi
// dung, ma khong dai vinh vien khi khong can.
//
// Dung chung cho: form them/sua chuyen xe, trang chi tiet (readonly), va
// bang xem truoc "Them nhanh tu anh" (cac dong tao dong bang JS luc runtime).
// =====================================================================
(function () {
  function chinhCao(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
  }

  function ganTuDongGian(el) {
    if (el.dataset.daGanTuDongGian) return;
    el.dataset.daGanTuDongGian = '1';
    chinhCao(el);
    el.addEventListener('input', function () { chinhCao(el); });
  }

  function quetCaTrang(goc) {
    (goc || document).querySelectorAll('textarea.tu-dong-gian').forEach(ganTuDongGian);
  }

  quetCaTrang();

  // Cac dong tao sau (bang xem truoc "Them nhanh tu anh", chi tiet realtime tu
  // cap nhat...) can duoc quet lai. Dung MutationObserver de tu bat, khong can
  // moi noi tao textarea moi phai nho goi lai ham nay.
  var qs = new MutationObserver(function (dsThayDoi) {
    dsThayDoi.forEach(function (td) {
      td.addedNodes.forEach(function (nut) {
        if (nut.nodeType !== 1) return;
        if (nut.matches && nut.matches('textarea.tu-dong-gian')) ganTuDongGian(nut);
        else if (nut.querySelectorAll) quetCaTrang(nut);
      });
    });
  });
  qs.observe(document.body, { childList: true, subtree: true });

  window.mcarTuDongGian = { quet: quetCaTrang, chinhCao: chinhCao };
})();
