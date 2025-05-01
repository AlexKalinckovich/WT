<?php

namespace services;

class MailService
{
    private PHPMailer $mailer;
    private string    $fromEmail;
    private string    $fromName;

    public function __construct(PHPMailer $mailer, string $fromEmail, string $fromName)
    {
        $this->mailer    = $mailer;
        $this->fromEmail = $fromEmail;
        $this->fromName  = $fromName;
    }

    public function sendRegistrationConfirmation(string $toEmail, string $userName, string $confirmUrl): void
    {
        $this->prepareBase($toEmail, "Подтверждение регистрации");
        // генерируем тело письма из шаблона:
        $body = $this->renderTemplate('emails/registration_confirm.mjml', [
            'userName'   => $userName,
            'confirmUrl' => $confirmUrl
        ]);
        $this->mailer->Body = $body;
        $this->send();
    }

    public function sendLoginNotification(string $toEmail, string $userName, \DateTimeInterface $loginAt): void
    {
        $this->prepareBase($toEmail, "Вход в аккаунт на вашем сайте");
        $body = $this->renderTemplate('emails/login_notify.mjml', [
            'userName' => $userName,
            'loginAt'  => $loginAt->format('Y-m-d H:i')
        ]);
        $this->mailer->Body = $body;
        $this->send();
    }

    private function prepareBase(string $to, string $subject): void
    {
        $m = $this->mailer;
        $m->isHTML(true);
        $m->setFrom($this->fromEmail, $this->fromName);
        $m->addAddress($to);
        $m->Subject = $subject;
    }

    private function renderTemplate(string $templatePath, array $vars): string
    {
        // 1) компиляция MJML → HTML (CLI или nodejs)
        // 2) вставка переменных (наш шаблонизатор или простой str_replace)
        // возвращает итоговый HTML
    }

    private function send(): void
    {
        if (! $this->mailer->send()) {
            throw new \RuntimeException("Mail error: " . $this->mailer->ErrorInfo);
        }
    }
}
