<?php

namespace App\Service;

use App\Entity\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
    private $mailer;

    private $url;
    private $host;
    private $username;
    private $password;
    private $port;
    private $from;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->url = "http://localhost:8000/";
        $this->host = 'mx-dc03.ewodi.net';
        $this->username = 'contact@ipocrate.org';
        $this->password = 'hervesiyou';
        $this->port = 587;
        $this->from = array();
    }

    public function activateAccount(User $user): array
    {

        $newUrl = $this->url . 'activate-account/' . $user->getId();
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->host;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->username;
            $this->mailer->Password = $this->password;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port =  $this->port;

            $this->mailer->setFrom('contact@ipocrate.org', 'Richbook Mail boot');
            $this->mailer->addAddress($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Activez votre compte dès maintenant!';
            $this->mailer->Body = '
                <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
                    <h2 style="color: #4CAF50;">Bienvenue, ' . $user->getFirstName() . ' ' . $user->getLastName() . ' !</h2>
                    <p>Nous sommes ravis de vous accueillir dans notre communauté.</p>
                    <p style="font-size: 16px;">Pour activer votre compte et profiter de toutes nos fonctionnalités, veuillez cliquer sur le bouton ci-dessous :</p>
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="' .  $newUrl  . '" style="
                            background-color: #4CAF50;
                            color: white;
                            padding: 10px 20px;
                            text-decoration: none;
                            font-weight: bold;
                            border-radius: 5px;
                            display: inline-block;">
                            Activer mon compte
                        </a>
                    </div>
                    <p>Si vous ne parvenez pas à cliquer sur le bouton, copiez et collez le lien suivant dans votre navigateur :</p>
                    <p style="color: #555; font-size: 14px;">' .  $newUrl  . '</p>
                    <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">
                    <p style="font-size: 12px; color: #777;">Si vous n\'êtes pas à l\'origine de cette demande, veuillez ignorer ce message.</p>
                </div>';

            // $this->mailer->AltBody = 'Bienvenue ' . $nom . '! Veuillez cliquer sur le lien suivant pour confirmer votre compte: ' . $url;

            $this->mailer->send();
            return ['status' => 'success', 'message' => 'Message has been sent'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}"];
        }
    }



    public function sendMail($url, $nom, $email): array
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = 'mx-dc03.ewodi.net';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'contact@ipocrate.org';
            $this->mailer->Password = 'hervesiyou';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;

            $this->mailer->setFrom('contact@ipocrate.org', 'Ipocrate.org');
            $this->mailer->addAddress($email, $nom);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Confirmation de votre compte';
            $this->mailer->Body = 'Bienvenue ' . $nom . '!<br>Veuillez cliquer sur le lien suivant pour confirmer votre compte: <a href="' . $url . '">CLIQUER ICI</a>';
            $this->mailer->AltBody = 'Bienvenue ' . $nom . '! Veuillez cliquer sur le lien suivant pour confirmer votre compte: ' . $url;

            $this->mailer->send();
            return ['status' => 'success', 'message' => 'Message has been sent'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}"];
        }
    }
    public function resetPassword($email): array
    {
        $resetUrl = $this->url . 'reset-password-update/?email='.$email;

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->host;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->username;
            $this->mailer->Password = $this->password;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $this->port;

            $this->mailer->setFrom('contact@ipocrate.org', 'Richbook Mail bot');
            $this->mailer->addAddress($email, 'M./Mme');

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Réinitialisez votre mot de passe';
            $this->mailer->Body = '
            <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
                <h2 style="color: #FF5722;">Bonjour, M./Mme !</h2>
                <p>Vous avez demandé à réinitialiser votre mot de passe.</p>
                <p style="font-size: 16px;">Pour continuer, cliquez sur le bouton ci-dessous :</p>
                <div style="text-align: center; margin: 20px 0;">
                    <a href="' . $resetUrl . '" style="
                        background-color: #FF5722;
                        color: white;
                        padding: 10px 20px;
                        text-decoration: none;
                        font-weight: bold;
                        border-radius: 5px;
                        display: inline-block;">
                        Réinitialiser mon mot de passe
                    </a>
                </div>
                <p>Si vous ne parvenez pas à cliquer sur le bouton, copiez et collez le lien suivant dans votre navigateur :</p>
                <p style="color: #555; font-size: 14px;">' . $resetUrl . '</p>
                <hr style="border: none; border-top: 1px solid #ccc; margin: 20px 0;">
                <p style="font-size: 12px; color: #777;">Si vous n\'êtes pas à l\'origine de cette demande, veuillez ignorer ce message.</p>
            </div>';

            $this->mailer->send();
            return ['status' => 'success', 'message' => 'Reset password email has been sent.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}"];
        }
    }
}
