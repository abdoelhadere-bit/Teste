<?php
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

// require __DIR__ . '/PHPMailer/PHPMailer.php';
// require __DIR__ . '/PHPMailer/SMTP.php';
// require __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($recipientEmail, $recipientName, $otp){
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = True;
        $mail->Username   = 'abdoelhadere3@gmail.com';
        $mail->Password   = 'sxvxgcklljmtxbja';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('abdoelhadere3@gmail.com', 'Smart Wallet');

        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Votre code OTP - Smart Wallet';

        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 50px auto;
                    background-color: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .content {
                    padding: 40px 30px;
                    text-align: center;
                }
                .otp-code {
                    font-size: 36px;
                    font-weight: bold;
                    color: #667eea;
                    letter-spacing: 8px;
                    margin: 30px 0;
                    padding: 20px;
                    background-color: #f0f0f0;
                    border-radius: 10px;
                    display: inline-block;
                }
                .footer {
                    background-color: #f8f8f8;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                .warning {
                    color: #e74c3c;
                    font-size: 14px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Smart Wallet</h1>
                    <p>Vérification de connexion</p>
                </div>
                <div class='content'>
                    <h2>Bonjour {$recipientName},</h2>
                    <p>Vous avez demandé à vous connecter à votre compte Smart Wallet.</p>
                    <p>Voici votre code de vérification :</p>
                    
                    <div class='otp-code'>{$otp}</div>
                    
                    <p>Ce code est valide pendant <strong>10 minutes</strong>.</p>
                    
                    <div class='warning'>
                        ⚠️ Si vous n'avez pas demandé ce code, ignorez cet email.
                    </div>
                </div>
                <div class='footer'>
                    <p>© 2024 Smart Wallet - Gestion financière sécurisée</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Bonjour {$recipientName},\n\n"
                       . "Votre code OTP est : {$otp}\n\n"
                       . "Ce code est valide pendant 10 minutes.\n\n"
                       . "Si vous n'avez pas demandé ce code, ignorez cet email.\n\n"
                       . "Smart Wallet";
        
        $mail->send();
        return true;
    } catch (Exception $e){
        error_log("Erreur d'envoi d'email : {$mail->ErrorInfo}");
        return false;
    }
}
