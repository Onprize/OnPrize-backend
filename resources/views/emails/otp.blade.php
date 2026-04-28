<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>On-Prize OTP</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px;">
        <tr>
            <td align="center">

                <!-- Card -->
                <table width="400" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; padding:30px; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding-bottom:20px;">
                            <h2 style="margin:0; color:#333;">On-Prize</h2>
                            <p style="margin:5px 0 0; color:#888; font-size:14px;">
                                Secure Verification
                            </p>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="color:#555; font-size:16px; text-align:center; padding-bottom:20px;">
                            Your One-Time Password (OTP) is:
                        </td>
                    </tr>

                    <!-- OTP Box -->
                    <tr>
                        <td align="center" style="padding-bottom:20px;">
                            <div style="
                                display:inline-block;
                                background:#f1f3f5;
                                padding:15px 30px;
                                font-size:28px;
                                font-weight:bold;
                                letter-spacing:5px;
                                border-radius:8px;
                                color:#000;
                            ">
                                {{ $otp }}
                            </div>
                        </td>
                    </tr>

                    <!-- Info -->
                    <tr>
                        <td style="color:#777; font-size:14px; text-align:center;">
                            This OTP is valid for <strong>10 minutes</strong>.<br>
                            Do not share this code with anyone.
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding-top:25px; text-align:center; font-size:12px; color:#aaa;">
                            © {{ date('Y') }} On-Prize. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>