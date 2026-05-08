<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xac nhan dat phong</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
        <tr>
            <td style="padding: 24px; background: #0f172a; color: #ffffff;">
                <h1 style="margin: 0; font-size: 24px;">Dasher Hotel</h1>
                <p style="margin: 8px 0 0 0; font-size: 14px; color: #cbd5e1;">Xac nhan yeu cau dat phong</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin: 0 0 12px 0; font-size: 16px;">Xin chao {{ $customerName ?: 'Quy khach' }},</p>
                <p style="margin: 0 0 16px 0; line-height: 1.6;">
                    Don dat phong <strong>{{ $booking->booking_code }}</strong> cua ban da duoc admin xac nhan thanh cong.
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px 0;"><strong>Ngay nhan phong:</strong> {{ optional($booking->check_in_expected)->format('d/m/Y') }}</p>
                    <p style="margin: 0 0 8px 0;"><strong>Ngay tra phong:</strong> {{ optional($booking->check_out_expected)->format('d/m/Y') }}</p>
                    <p style="margin: 0;"><strong>Tien coc da ghi nhan:</strong> {{ number_format((float) $booking->deposit_amount, 0, ',', '.') }} VND</p>
                </div>

                <p style="margin: 0 0 16px 0; line-height: 1.6;">
                    Vui long mang theo <strong>CCCD/giay to tuy than hop le</strong> khi den check-in de thu tuc duoc xu ly nhanh.
                </p>

                <p style="margin: 0; line-height: 1.6;">
                    Cam on ban da tin tuong Dasher Hotel. Hen gap ban som!
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
