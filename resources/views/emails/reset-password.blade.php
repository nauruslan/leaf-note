<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля - Leaf Note</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center; border-bottom: 1px solid #e5e7eb;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #1f2937;">
                                Leaf Note
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600; color: #1f2937;">
                                Здравствуйте, {{ $userName }}!
                            </h2>

                            <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6; color: #4b5563;">
                                Мы получили запрос на сброс пароля для вашего аккаунта в Leaf Note. Для установки нового
                                пароля нажмите на кнопку ниже:
                            </p>

                            <!-- Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $resetUrl }}"
                                            style="display: inline-block; background: linear-gradient(to right, #4f46e5, #9333ea);
                                                  color: #ffffff;
                                                  font-weight: 500;
                                                  text-decoration: none;
                                                  padding: 12px 32px;
                                                  border-radius: 8px;
                                                  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                                                  font-size: 16px;">
                                            Сбросить пароль
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #6b7280;">
                                Если кнопка не работает, скопируйте и вставьте следующую ссылку в адресную строку вашего
                                браузера:
                            </p>

                            <p
                                style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #6b7280; word-break: break-all;">
                                <a href="{{ $resetUrl }}"
                                    style="color: #4f46e5; text-decoration: underline;">{{ $resetUrl }}</a>
                            </p>

                            <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #6b7280;">
                                Ссылка действительна в течение 60 минут. Если вы не запрашивали сброс пароля, просто
                                проигнорируйте это письмо.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px 40px 40px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 8px 0; font-size: 14px; color: #6b7280; text-align: center;">
                                С уважением, команда Leaf Note
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center;">
                                Это автоматическое письмо, пожалуйста, не отвечайте на него.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
