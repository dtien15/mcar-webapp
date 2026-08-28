// =====================================================================
// tro-giup.js - Nut "Tro giup" chay TOUR DAN DUONG (driver.js) ngay tren
// trang dang xem, chi vao tung nut that su ton tai luc do.
//
// Khac voi trang /huongdan (doc lai duoc bat cu luc nao, danh cho nguoi
// muon xem lai tu dau): tour o day chi giai thich CAC NUT CUA DUNG TRANG
// hien tai, cho nguoi lan dau mo trang do khong biet bam vao dau.
//
// Nguoi dung cua app nay phan lon lon tuoi, it quen cong nghe - vi vay:
//   - Cau chu ngan, don gian, khong dung thuat ngu
//   - Buoc nao khong tim thay phan tu tren trang thi TU DONG BO QUA (vd
//     dong "Chua giao" hay bong chat co the chua load kip) - khong de tour
//     bi ket cung mot cho lam nguoi dung boi roi hon.
// =====================================================================
(function () {
  var nutTroGiup = document.getElementById('nutTroGiup');
  if (!nutTroGiup || typeof window.driver === 'undefined') return;

  var trang   = window.mcarTrangHienTai || '';
  var vaiTro  = window.mcarVaiTro || '';
  var laTaiXe = vaiTro === 'taixe';

  /**
   * Danh sach tour theo TUNG TRANG. Moi tour la mang cac buoc:
   *   { chon: 'selector CSS', tieuDe, moTa }
   * Neu chon la '' (rong) thi la buoc gioi thieu chung, khong ghim vao nut nao.
   *
   * Chi dinh nghia cho selector CHAC CHAN co trong HTML (khong phai the tao
   * dong bang JS luc runtime nhu modal), de tour luon tim thay dung cho.
   */
  function tourTongQuan() {
    return [
      { chon: '.thanh-ben-menu', tieuDe: 'Menu bên trái', moTa: 'Đây là menu chính — bấm vào tên mục để chuyển trang. Mục nào có số đỏ nghĩa là đang có việc cần xử lý.' },
      { chon: '#nutTroGiup', tieuDe: 'Nút Trợ giúp', moTa: 'Bấm nút này ở bất kỳ trang nào để xem hướng dẫn nhanh của đúng trang đó.' },
      { chon: '.nut-chuong', tieuDe: 'Thông báo', moTa: 'Chấm đỏ nghĩa là có thông báo mới chưa đọc — bấm vào chuông để xem.' },
      { chon: '#nutBongChat', tieuDe: 'Nhắn tin', moTa: 'Bấm vào đây để nhắn tin trực tiếp, giống như nhắn Zalo vậy.' }
    ];
  }

  function tourChuyenXeQuanLy() {
    return [
      { chon: 'a[href*="chuyenxe/them"]', tieuDe: 'Thêm 1 chuyến xe', moTa: 'Bấm vào đây khi cần tạo một chuyến xe mới, điền đầy đủ thông tin.' },
      { chon: '[data-bs-target="#themNhanh"]', tieuDe: 'Thêm nhiều chuyến từ ảnh', moTa: 'Có lịch trình dài nhiều chặng? Dán ảnh hoặc tin nhắn vào đây, máy sẽ đọc giúp và tạo nhiều chuyến cùng lúc.' },
      { chon: '.nhan-tab-trang-thai', tieuDe: 'Lọc theo trạng thái', moTa: 'Bấm vào các tab này để chỉ xem chuyến đang chờ xử lý, đã hoàn thành, hay đã hủy.' },
      { chon: 'tbody#dsDongBang tr:first-child .o-trang-thai, .ds-the-dien-thoai .the-chuyen-xe:first-child .huy-hieu-trang-thai',
        tieuDe: 'Trạng thái chuyến xe', moTa: 'Xem nhanh chuyến đang ở bước nào. Biểu tượng nhỏ bên cạnh là các lưu ý thêm — rê chuột vào để đọc.' },
      { chon: 'tbody#dsDongBang tr:first-child .cum-thao-tac, .ds-the-dien-thoai .the-chuyen-xe:first-child .cum-thao-tac',
        tieuDe: 'Các nút thao tác', moTa: 'Nút màu là việc cần làm ngay với chuyến đó. Bấm "⋯" để xem thêm các việc khác như Sửa, Hủy.' }
    ];
  }

  function tourChuyenXeTaiXe() {
    return [
      { chon: '.ds-the-dien-thoai .the-chuyen-xe:first-child, tbody#dsDongBang tr:first-child',
        tieuDe: 'Chuyến xe của bạn', moTa: 'Đây là các chuyến được giao cho bạn, mới nhất nằm trên cùng.' },
      { chon: '.ds-the-dien-thoai .the-chuyen-xe:first-child .cum-thao-tac, tbody#dsDongBang tr:first-child .cum-thao-tac',
        tieuDe: 'Nút thao tác', moTa: 'Chạy xong bấm "Nhập & Xác nhận" để điền số tiền thực tế. Có việc đột xuất thì bấm "⋯" để báo khách hủy hoặc nhờ người khác chạy.' }
    ];
  }

  function tourLuong() {
    return [
      { chon: '.btn-outline-success', tieuDe: 'Tính lại lương', moTa: 'Lương tự tính khi chuyến được chốt — bấm nút này chỉ khi thấy số liệu có gì đó chưa đúng, cần tính lại toàn bộ.' },
      { chon: '#luongNoiDung table', tieuDe: 'Bảng lương', moTa: 'Bấm vào tên tài xế để xem phiếu lương chi tiết, in ra được. Số dương là công ty còn nợ, số âm là tài xế còn nợ công ty.' }
    ];
  }

  function tourThanhToan() {
    return [
      { chon: '.nav-tabs', tieuDe: 'Hai mục chính', moTa: '"Khoản chi công ty" để ghi các khoản chi không liên quan tài xế. "Công nợ tài xế" để xem và trả lương.' },
      { chon: '#tabChi .btn-primary', tieuDe: 'Ghi khoản chi mới', moTa: 'Điền ngày, nội dung, số tiền rồi bấm nút này để lưu lại một khoản chi.' }
    ];
  }

  function tourBaoCao() {
    return [
      { chon: 'input[name="tu_ngay"]', tieuDe: 'Chọn khoảng ngày', moTa: 'Chọn từ ngày - đến ngày muốn xem rồi bấm Lọc.' },
      { chon: 'a[href*="xuatcsv"], a[href*="xuatlailo"]', tieuDe: 'Xuất Excel', moTa: 'Bấm để tải số liệu về máy, mở bằng Excel xem hoặc lưu lại.' }
    ];
  }

  function tourDanhMuc() {
    return [
      { chon: 'button.btn-primary, a.btn-primary', tieuDe: 'Thêm mới', moTa: 'Điền thông tin vào form rồi bấm nút này để lưu lại.' }
    ];
  }

  var dsTour = {
    'chuyenxe': laTaiXe ? tourChuyenXeTaiXe : tourChuyenXeQuanLy,
    'luong':     tourLuong,
    'thanhtoan': tourThanhToan,
    'baocao':    tourBaoCao,
    'xe':        tourDanhMuc,
    'taixe':     laTaiXe ? null : tourDanhMuc,
    'loaikeo':   tourDanhMuc,
    'banggia':   tourDanhMuc
  };

  function layTour() {
    var timTheoTrang = dsTour[trang];
    var buoc = timTheoTrang ? timTheoTrang() : [];
    // Luon ket thuc bang vai net chung (menu, chuong, chat) - dung cho moi trang
    return buoc.concat(trang === 'tongquan' || !timTheoTrang ? tourTongQuan() : []);
  }

  /**
   * Mot so buoc dung selector gop CA 2 GIAO DIEN (bang may tinh + the mobile),
   * vi du 'tbody tr .o-trang-thai, .the-chuyen-xe .huy-hieu-trang-thai' - luc
   * nao cung chi MOT trong hai ben dang hien (ben kia bi an bang display:none).
   *
   * document.querySelector() voi danh sach nhieu selector chon phan tu dau
   * tien theo THU TU TRONG HTML, khong quan tam no co dang an hay khong - neu
   * ban mobile nam truoc ban may tinh trong HTML (du dang an tren man rong),
   * no se bi chon nham, khien driver.js ghim tour vao mot phan tu co kich
   * thuoc 0x0 (popover chay ve goc trai man hinh). Ham nay quet qua TAT CA
   * phan tu khop, chi lay phan tu dang thuc su hien (offsetParent != null).
   */
  function phanTuDangHien(chon) {
    var ds = document.querySelectorAll(chon);
    for (var i = 0; i < ds.length; i++) {
      if (ds[i].offsetParent !== null) return ds[i];
    }
    return null;
  }

  nutTroGiup.addEventListener('click', function () {
    var buoc = layTour().filter(function (b) {
      return !b.chon || phanTuDangHien(b.chon);
    });

    if (!buoc.length) {
      alert('Trang này chưa có hướng dẫn nhanh. Xem đầy đủ ở mục "Hướng dẫn sử dụng" trên menu bên trái nhé.');
      return;
    }

    var dsBuoc = buoc.map(function (b) {
      return b.chon
        ? { element: phanTuDangHien(b.chon), popover: { title: b.tieuDe, description: b.moTa } }
        : { popover: { title: b.tieuDe, description: b.moTa } };
    });

    window.driver.js.driver({
      showProgress: true,
      allowClose: true,
      popoverClass: 'mcar-popover',
      nextBtnText: 'Tiếp theo →',
      prevBtnText: '← Trước',
      doneBtnText: 'Xong',
      progressText: '{{current}} / {{total}}',
      steps: dsBuoc
    }).drive();
  });
})();
