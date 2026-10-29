# Hướng dẫn cho coding agent

## Trước khi làm việc
- Đọc `BUSINESS_OVERVIEW.md` để hiểu nghiệp vụ, module, luồng xử lý và quyền.
- Đọc thêm các file trong `docs/` nếu có nội dung liên quan đến yêu cầu.
- Kiểm tra code thực tế trước khi kết luận; tài liệu có thể chưa cập nhật.
- Nếu tài liệu và code mâu thuẫn, nêu rõ điểm mâu thuẫn; không tự chọn một bên làm sự thật.

## Quy tắc thay đổi
- Không tự tạo business rule, quyền, trạng thái hoặc quan hệ dữ liệu nếu chưa có căn cứ.
- Khi chưa rõ, tìm trong code, migration, policy, middleware, request validation và test.
- Giữ tương thích với cấu trúc và convention hiện có của Laravel project.
- Không sửa file ngoài phạm vi yêu cầu.
- Nếu thay đổi ảnh hưởng business flow, model, permission hoặc thuật ngữ, cập nhật `BUSINESS_OVERVIEW.md`.

## Kiểm tra
- Xem các lệnh cài đặt, chạy test, format và lint trong `composer.json`, `package.json` hoặc README.
- Chạy các kiểm tra phù hợp với phần code đã sửa.
- Báo rõ kiểm tra nào đã chạy và kết quả.

## Kết quả
- Tóm tắt file đã sửa và lý do.
- Nêu các giả định hoặc điểm nghiệp vụ chưa xác định.
