<?php
/**
 * Trang "Huong dan su dung" - noi TRA CUU LAI duoc bat cu luc nao.
 *
 * Chia lam 2 khoi lon: "Danh cho Quan ly / Ke toan" va "Danh cho Tai xe".
 * Nguoi dang nhap la vai tro nao thi khoi cua vai tro do tu mo san, khoi
 * con lai gap lai (van xem duoc, phong khi quan ly muon biet tai xe thay
 * gi de con giai thich cho ho).
 *
 * Nhan vao: $laQuanLyXem, $laQuanTri
 */
?>
<div class="the">
  <div class="the-than">
    <div class="d-flex align-items-start gap-3 flex-wrap">
      <div class="bieu-tuong nen-xanh" style="width:48px;height:48px;font-size:22px;flex:none">
        <?= bieuTuong('help-circle') ?>
      </div>
      <div>
        <h5 class="mb-1">Chào bạn 👋</h5>
        <div class="text-muted" style="font-size:14px; max-width:640px">
          Trang này giải thích từng nút, từng chức năng trong app — đọc lại được bất cứ lúc nào,
          không cần nhớ hết một lần. Bấm vào tiêu đề mỗi mục để mở ra xem.
          Chỗ nào chưa rõ, cứ nhắn hỏi người quản lý.
        </div>
      </div>
    </div>
  </div>
</div>

<div class="accordion mt-3" id="accHuongDan">

  <!-- ============================================================= -->
  <!-- KHOI 1: DANH CHO QUAN LY / KE TOAN                             -->
  <!-- ============================================================= -->
  <div class="the mb-3">
    <div class="the-dau">
      <span><?= bieuTuong('briefcase') ?> Dành cho Quản lý / Kế toán</span>
    </div>
    <div class="the-than the-than-khong-dem">
      <div class="accordion" id="accQuanLy">

        <?php
        /**
         * $muc: mang cac muc huong dan.
         * Moi phan tu: [id, tieu de, noi dung HTML].
         * Viet noi dung bang the <ol>/<ul> ro rang, cau ngan, tranh thuat ngu.
         */
        $mucQuanLy = [
          [
            'qlThemChuyen', '🚗 Thêm một chuyến xe',
            '<ol class="ds-huong-dan">
              <li>Vào mục <strong>Chuyến xe</strong> ở menu bên trái.</li>
              <li>Bấm nút xanh <strong>+ Thêm chuyến xe</strong> ở góc trên bên phải.</li>
              <li>Điền ngày chạy, giờ đón, hành trình, chọn xe và tài xế.</li>
              <li>Điền các khoản tiền nếu đã biết trước (khách trả, tiền cuốc…) — không biết thì để trống, tài xế nhập sau khi chạy xong.</li>
              <li>Bấm <strong>Lưu chuyến xe</strong> ở cuối trang.</li>
              <li>Tài xế sẽ nhận được thông báo ngay trên điện thoại.</li>
            </ol>'
          ],
          [
            'qlThemNhanh', '✨ Thêm nhiều chuyến cùng lúc từ ảnh (AI đọc giúp)',
            '<p>Dùng khi khách gửi lịch trình dài (nhiều ngày, nhiều chặng) qua ảnh chụp hoặc tin nhắn Zalo — đỡ phải gõ tay từng chuyến.</p>
            <ol class="ds-huong-dan">
              <li>Vào <strong>Chuyến xe</strong>, bấm nút <strong>Thêm nhanh từ ảnh</strong> (màu xanh dương).</li>
              <li>Dán ảnh vào (bấm Ctrl+V sau khi đã chụp/copy ảnh), hoặc bấm vào ô để chọn file ảnh. Nếu không có ảnh, gõ thẳng đoạn tin nhắn vào ô bên phải.</li>
              <li>Bấm <strong>Phân tích</strong>, chờ vài giây để máy đọc.</li>
              <li>Kiểm tra lại bảng hiện ra — sửa thẳng vào ô nếu máy đọc sai chỗ nào. Bỏ tick dòng nào không muốn tạo.</li>
              <li>Bấm <strong>Tạo chuyến</strong>.</li>
            </ol>
            <div class="alert alert-warning mb-0" style="font-size:13px">
              ⚠️ Các chuyến tạo theo cách này <strong>chưa có tài xế</strong> — máy không tự đoán giao cho ai.
              Xem mục bên dưới để giao tài xế.
            </div>'
          ],
          [
            'qlGiaoTaiXe', '👤 Giao tài xế cho chuyến "Chưa giao"',
            '<ol class="ds-huong-dan">
              <li>Trong danh sách <strong>Chuyến xe</strong>, tìm dòng có nhãn màu cam <strong>Chưa giao</strong>.</li>
              <li>Ở cột Tài xế của dòng đó, bấm vào ô chọn, chọn tên tài xế.</li>
              <li>Bấm nút <strong>Giao</strong> màu xanh dương vừa hiện ra.</li>
              <li>Xác nhận lại một lần nữa — xong, tài xế nhận thông báo ngay.</li>
            </ol>'
          ],
          [
            'qlChot', '✅ Chốt chuyến xe (sau khi tài xế đã xác nhận)',
            '<p>Tài xế chạy xong sẽ tự nhập số liệu và xác nhận. Việc của quản lý là kiểm tra rồi <strong>chốt</strong> lại — chốt xong tiền mới chính thức tính vào lương.</p>
            <ol class="ds-huong-dan">
              <li>Vào <strong>Chuyến xe</strong>, lọc theo tab <strong>Tài xế đã xác nhận</strong> để dễ tìm.</li>
              <li>Bấm <strong>Chi tiết</strong> để xem kỹ số liệu tài xế nhập (nếu cần).</li>
              <li>Thấy đúng rồi thì bấm nút xanh lá <strong>Chốt</strong> ngay trên dòng đó.</li>
              <li>Muốn sửa lại số liệu thì bấm <strong>Sửa</strong> trước khi chốt.</li>
            </ol>
            <div class="alert alert-secondary mb-0" style="font-size:13px">
              💡 Chốt xong lỡ thấy sai vẫn <strong>mở lại được</strong>: bấm menu <code>⋯</code> ở dòng đó → <strong>Mở lại chuyến</strong>.
            </div>'
          ],
          [
            'qlHuy', '🚫 Hủy chuyến khi khách đổi lịch / không đi nữa',
            '<ol class="ds-huong-dan">
              <li>Ở dòng chuyến cần hủy, bấm menu <code>⋯</code> → <strong>Hủy chuyến</strong>.</li>
              <li>Chọn <strong>hủy ở giai đoạn nào</strong>: chưa đi (thường không mất gì), tài xế đã tới điểm đón, hay đang trên đường.</li>
              <li>Nếu tài xế đã tốn công/xăng thì điền thêm <strong>khách đền bù</strong> và <strong>công ty bù cho tài xế</strong> — không thì để trống.</li>
              <li>Ghi lý do hủy (không bắt buộc nhưng nên ghi để sau còn nhớ).</li>
              <li>Bấm <strong>Xác nhận hủy</strong>.</li>
            </ol>
            <p class="mb-0">Chuyến đã hủy vẫn còn trong danh sách (tab <strong>Đã hủy</strong>), lỡ hủy nhầm thì bấm <strong>Bỏ hủy</strong> để khôi phục.</p>'
          ],
          [
            'qlNopLai', '💵 Xác nhận tài xế đã nộp lại tiền',
            '<p>Áp dụng cho chuyến tài xế thu tiền mặt của khách rồi mang về nộp lại công ty.</p>
            <ol class="ds-huong-dan">
              <li>Chuyến nào tài xế đang giữ tiền sẽ có nhãn <strong>💵 Chưa nộp lại</strong> (biểu tượng màu cam).</li>
              <li>Khi tài xế đã đưa tiền mặt/chuyển khoản xong, bấm nút xanh lá <strong>Đã nộp lại</strong> trên dòng đó.</li>
              <li>Chọn hình thức nộp (tiền mặt / chuyển khoản) rồi xác nhận.</li>
            </ol>'
          ],
          [
            'qlTrungLich', '⚠️ Ý nghĩa nhãn "Trùng lịch"',
            '<p>Khi thấy nhãn màu vàng <strong>⚠ Trùng lịch</strong> trên một chuyến, nghĩa là <strong>cùng ngày đó xe hoặc tài xế này còn một chuyến khác nữa</strong> — rê chuột vào biểu tượng để xem chi tiết chuyến nào đang đụng nhau.</p>
            <p class="mb-0">Đây chỉ là <strong>lời nhắc</strong>, không phải lỗi — một xe chạy 2 chuyến trong ngày vẫn bình thường nếu sắp xếp kịp giờ. Quản lý tự xem có ổn không.</p>'
          ],
          [
            'qlLuong', '💰 Xem và tính lương tài xế',
            '<ol class="ds-huong-dan">
              <li>Vào mục <strong>Bảng lương</strong> ở menu bên trái.</li>
              <li>Chọn tháng/năm cần xem ở trên cùng.</li>
              <li>Lương tự tính từ các chuyến <strong>đã chốt</strong> trong tháng đó — không cần bấm nút tính nào cả, cứ chốt chuyến xong là số tự cập nhật.</li>
              <li>Bấm vào tên tài xế để xem <strong>phiếu lương chi tiết</strong> (in ra được).</li>
              <li>Cột "Còn lại": số dương là <strong>công ty còn nợ tài xế</strong>, số âm là <strong>tài xế còn nợ công ty</strong>.</li>
            </ol>'
          ],
          [
            'qlThanhToan', '🧾 Thanh toán lương & xem công nợ',
            '<ol class="ds-huong-dan">
              <li>Vào mục <strong>Thanh toán & công nợ</strong>.</li>
              <li>Tab <strong>Công nợ tài xế</strong>: xem ai đang được nợ bao nhiêu, ai đang nợ công ty.</li>
              <li>Trả lương cho tài xế xong thì bấm <strong>Thanh toán</strong> ở dòng người đó, ghi số tiền đã trả — số "còn lại" sẽ tự trừ đi.</li>
              <li>Tab <strong>Khoản chi công ty</strong>: ghi lại các khoản công ty chi ra (không liên quan tới tài xế) như tiền văn phòng, sửa xe lớn…</li>
            </ol>'
          ],
          [
            'qlBaoCaoDoanhThu', '📊 Xem báo cáo doanh thu',
            '<p>Vào mục <strong>Báo cáo doanh thu</strong> — xem tổng số tiền khách trả theo tháng, theo xe, theo tài xế, theo loại kèo. Chọn khoảng ngày ở trên để lọc theo ý muốn. Có nút <strong>Xuất Excel</strong> để tải về lưu.</p>'
          ],
          [
            'qlBaoCaoLaiLo', '📈 Xem báo cáo lãi lỗ',
            '<p>Khác với báo cáo doanh thu (chỉ tính tiền khách trả), báo cáo <strong>lãi lỗ</strong> đã <strong>trừ hết chi phí</strong> (kèo ngoài, tiền cuốc, xăng dầu, bảo dưỡng…) để biết công ty thực sự lời bao nhiêu.</p>
            <p class="mb-0">Vào mục <strong>Báo cáo lãi lỗ</strong>, xem phần "Xe nào có lãi" để biết xe/kèo nào đang thực sự có lời, xe/kèo nào đang lỗ.</p>'
          ],
          [
            'qlDanhMuc', '📁 Quản lý Xe / Tài xế / Loại kèo / Bảng giá',
            '<p>Các mục này ở nhóm <strong>DANH MỤC</strong> trên menu — dùng để khai báo trước những thứ sẽ chọn khi tạo chuyến xe:</p>
            <ul class="ds-huong-dan">
              <li><strong>Xe</strong>: danh sách xe của công ty (tên, biển số, số chỗ).</li>
              <li><strong>Tài xế</strong>: thông tin tài xế, lương cơ bản, bảo hiểm, xe mặc định.</li>
              <li><strong>Loại kèo</strong>: các kiểu hợp đồng/loại chuyến (kèo công ty, kèo ngoài…).</li>
              <li><strong>Bảng giá</strong>: giá tham khảo cho từng tuyến.</li>
            </ul>
            <p class="mb-0">Mỗi mục đều có nút <strong>+ Thêm mới</strong> ở góc trên, và nút Sửa/Xóa trên từng dòng.</p>'
          ],
          [
            'qlNguoiDung', '🔑 Tạo tài khoản đăng nhập cho tài xế',
            '<ol class="ds-huong-dan">
              <li>Vào mục <strong>Người dùng</strong> (chỉ Quản trị viên mới thấy mục này).</li>
              <li>Bấm <strong>+ Thêm mới</strong>.</li>
              <li>Đặt tên đăng nhập, mật khẩu, chọn vai trò <strong>Tài xế</strong> và chọn đúng tài xế tương ứng trong danh sách.</li>
              <li>Gửi tên đăng nhập + mật khẩu cho tài xế đó qua Zalo/tin nhắn.</li>
            </ol>
            <div class="alert alert-secondary mb-0" style="font-size:13px">
              💡 Tài xế chưa có tài khoản thì <strong>không nhận được thông báo</strong> và không tự xác nhận chuyến trên điện thoại được — trang Theo dõi hệ thống sẽ nhắc nếu có tài xế nào đang thiếu tài khoản.
            </div>'
          ],
          [
            'qlChat', '💬 Nhắn tin với tài xế',
            '<ol class="ds-huong-dan">
              <li>Bấm vào <strong>bong bóng chat</strong> tròn màu xanh ở góc dưới bên phải màn hình (thấy được ở mọi trang).</li>
              <li>Chọn tên tài xế muốn nhắn.</li>
              <li>Gõ tin nhắn như nhắn Zalo bình thường.</li>
            </ol>
            <p class="mb-0">Ngoài ra ở mỗi dòng chuyến xe cũng có nút hình bong bóng nhỏ 💬 — bấm vào để nhắn ngay về đúng chuyến đó, tin nhắn vẫn nằm chung một hội thoại với tài xế.</p>'
          ],
          [
            'qlThongBao', '🔔 Xem thông báo',
            '<p>Bấm vào biểu tượng <strong>chuông 🔔</strong> ở góc trên bên phải để xem các thông báo mới (tài xế xác nhận chuyến, báo khách hủy…). Chấm đỏ trên chuông nghĩa là có thông báo chưa đọc.</p>'
          ],
          [
            'qlCaiDat', '⚙️ Cài đặt tỷ giá ngoại tệ & AI',
            '<p>Vào mục <strong>Cài đặt</strong> (chỉ Quản trị viên):</p>
            <ul class="ds-huong-dan">
              <li><strong>Tỷ giá USD/EUR</strong>: khai để hệ thống tự quy đổi tiền khách trả bằng ngoại tệ ra VNĐ khi tính lương, báo cáo.</li>
              <li><strong>API key OpenAI</strong>: cần khai để dùng được tính năng "Thêm nhanh từ ảnh" và "Phân tích AI" — hỏi người kỹ thuật để lấy key này.</li>
            </ul>'
          ],
          [
            'qlHeThong', '🗑️ Theo dõi hệ thống & thùng rác (xóa dữ liệu)',
            '<p>Mục này (chỉ Quản trị viên) là <strong>nơi duy nhất</strong> xóa được chuyến xe, thông báo, tin nhắn — để tránh bấm nhầm ở chỗ khác.</p>
            <ol class="ds-huong-dan">
              <li>Vào <strong>Theo dõi hệ thống</strong>, kéo xuống mục <strong>Quản lý dữ liệu</strong>.</li>
              <li>Chọn tab muốn xem (Chuyến xe / Thông báo / Tin nhắn / Thùng rác).</li>
              <li>Bấm <strong>Xóa</strong> trên dòng cần xóa. Chuyến xe xóa xong vẫn nằm trong <strong>Thùng rác</strong> 30 ngày, khôi phục lại được nếu lỡ tay.</li>
            </ol>
            <p class="mb-0">Phần trên của trang này còn tự động cảnh báo nếu phát hiện điều bất thường (dữ liệu lệch, tài xế chưa có tài khoản…).</p>'
          ],
        ];

        foreach ($mucQuanLy as $i => [$idMuc, $tieuDe, $noiDung]):
        ?>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button <?= $i === 0 && $laQuanLyXem ? '' : 'collapsed' ?>" type="button"
                      data-bs-toggle="collapse" data-bs-target="#<?= $idMuc ?>">
                <?= $tieuDe ?>
              </button>
            </h2>
            <div id="<?= $idMuc ?>" class="accordion-collapse collapse <?= $i === 0 && $laQuanLyXem ? 'show' : '' ?>"
                 data-bs-parent="#accQuanLy">
              <div class="accordion-body"><?= $noiDung ?></div>
            </div>
          </div>
        <?php endforeach; ?>

      </div>
    </div>
  </div>

  <!-- ============================================================= -->
  <!-- KHOI 2: DANH CHO TAI XE                                        -->
  <!-- ============================================================= -->
  <div class="the mb-3">
    <div class="the-dau">
      <span><?= bieuTuong('steering-wheel') ?> Dành cho Tài xế</span>
    </div>
    <div class="the-than the-than-khong-dem">
      <div class="accordion" id="accTaiXe">

        <?php
        $mucTaiXe = [
          [
            'txXemChuyen', '📋 Xem chuyến xe được giao',
            '<p>Mở app lên, mục <strong>Chuyến xe</strong> sẽ hiện ngay — đây là chuyến đang được giao cho bạn. Chuyến mới sẽ có nhãn <strong>Mới giao</strong> và bạn sẽ nhận được thông báo trên điện thoại.</p>'
          ],
          [
            'txXacNhan', '✍️ Nhập chi phí & xác nhận sau khi chạy xong',
            '<ol class="ds-huong-dan">
              <li>Sau khi chở khách xong, mở chuyến đó ra, bấm nút <strong>Nhập & Xác nhận</strong>.</li>
              <li>Điền đúng số tiền thực tế: khách trả bao nhiêu, ai là người thu tiền (bạn thu tiền mặt hay công ty thu thẳng), tiền xăng dầu đã đổ…</li>
              <li>Nếu có phụ phí phát sinh (lưu đêm, chạy khuya…) thì điền vào ô Phụ phí.</li>
              <li>Kiểm tra lại số liệu cho đúng rồi bấm <strong>Xác nhận</strong>.</li>
            </ol>
            <div class="alert alert-warning mb-0" style="font-size:13px">
              ⚠️ Điền đúng mục <strong>"Ai thu tiền khách"</strong> rất quan trọng — chọn sai sẽ tính nhầm tiền vào lương của bạn.
              Chọn <strong>"Tài xế thu tiền mặt"</strong> nếu bạn đang cầm tiền khách, chọn <strong>"Công ty thu"</strong> nếu khách chuyển khoản thẳng cho công ty (bạn không hề cầm tiền).
            </div>'
          ],
          [
            'txSuaPhuPhi', '🔧 Sửa lại phụ phí (nếu điền thiếu/sai)',
            '<p>Sau khi đã xác nhận nhưng công ty <strong>chưa chốt</strong> chuyến, bạn vẫn sửa lại được phụ phí: bấm <strong>Kiểm tra / Sửa phụ phí</strong> trên chuyến đó. Chuyến đã bị công ty chốt rồi thì không tự sửa được nữa — nhắn báo quản lý.</p>'
          ],
          [
            'txNhoChay', '🤝 Nhờ tài xế khác chạy giùm',
            '<p>Có việc đột xuất không chạy được chuyến đã nhận (chưa xác nhận)? Bấm <strong>Nhờ tài xế khác chạy</strong>, chọn tên người sẽ chạy thay — chuyến đó sẽ chuyển hẳn sang cho họ, xe giữ nguyên.</p>'
          ],
          [
            'txBaoHuy', '📢 Báo khách hủy chuyến',
            '<p>Khách báo hủy/đổi lịch thì bấm nút <strong>Báo khách hủy</strong> trên chuyến đó, ghi rõ lý do. Công ty sẽ nhận được báo ngay và xác nhận hủy — <strong>bạn không tự hủy được</strong> chuyến, vì việc hủy có liên quan tới tiền bù/đền nên phải để quản lý xử lý.</p>
            <p class="mb-0">💡 Nếu bạn đã chạy tới điểm đón rồi khách mới hủy, nhớ ghi rõ trong lý do để công ty biết mà bù công cho bạn.</p>'
          ],
          [
            'txChiTiet', '📄 Xem chi tiết một chuyến',
            '<p>Bấm nút <strong>Chi tiết</strong> trên bất kỳ chuyến nào để xem đầy đủ thông tin: lộ trình, số tiền, lịch sử xử lý (ai xác nhận lúc nào, công ty chốt lúc nào…).</p>'
          ],
          [
            'txChat', '💬 Nhắn tin với công ty',
            '<p>Bấm vào <strong>bong bóng chat</strong> tròn màu xanh ở góc dưới màn hình để nhắn trực tiếp với quản lý — giống như nhắn Zalo bình thường. Bấm nút bong bóng nhỏ 💬 trên một chuyến cụ thể để nhắn ngay về chuyến đó.</p>'
          ],
          [
            'txThongBao', '🔔 Xem thông báo',
            '<p>Bấm vào <strong>chuông 🔔</strong> ở góc trên màn hình để xem thông báo: chuyến mới được giao, chuyến bị hủy, tin nhắn mới từ công ty… Chấm đỏ nghĩa là có thông báo chưa đọc.</p>'
          ],
          [
            'txDoiMatKhau', '🔒 Đổi mật khẩu',
            '<ol class="ds-huong-dan">
              <li>Bấm vào tên bạn ở góc trên bên phải màn hình.</li>
              <li>Chọn <strong>Đổi mật khẩu</strong>.</li>
              <li>Nhập mật khẩu cũ, rồi nhập mật khẩu mới 2 lần cho khớp nhau.</li>
              <li>Bấm <strong>Lưu</strong>.</li>
            </ol>'
          ],
        ];

        foreach ($mucTaiXe as $i => [$idMuc, $tieuDe, $noiDung]):
        ?>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button <?= $i === 0 && !$laQuanLyXem ? '' : 'collapsed' ?>" type="button"
                      data-bs-toggle="collapse" data-bs-target="#<?= $idMuc ?>">
                <?= $tieuDe ?>
              </button>
            </h2>
            <div id="<?= $idMuc ?>" class="accordion-collapse collapse <?= $i === 0 && !$laQuanLyXem ? 'show' : '' ?>"
                 data-bs-parent="#accTaiXe">
              <div class="accordion-body"><?= $noiDung ?></div>
            </div>
          </div>
        <?php endforeach; ?>

      </div>
    </div>
  </div>

  <div class="the">
    <div class="the-than text-center text-muted" style="font-size:13px">
      Vẫn chưa rõ chỗ nào? Nhắn tin ngay qua bong bóng chat ở góc màn hình để được hỗ trợ.
    </div>
  </div>

</div>
