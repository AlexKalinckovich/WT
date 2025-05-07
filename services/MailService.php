<?php
declare(strict_types=1);

namespace services;

use Exception;
use MyTemplate\TemplateFacade;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use utils\Logger;
use utils\SingletonTrait;

class MailService
{
    use SingletonTrait;
    private PHPMailer       $mailer;
    private TemplateFacade  $templateFacade;

    protected function __construct(TemplateFacade $templateFacade) {
        $this->mailer = new PHPMailer(true);
        $this->templateFacade = $templateFacade;
    }

    /**
     * Отправляет письмо с подтверждением регистрации.
     *
     * @param string $toEmail Email получателя
     * @param string $userName Имя пользователя (для персонализации)
     * @param string $confirmUrl Ссылка для подтверждения регистрации
     *
     * @throws MailException
     * @throws Exception
     */
    public function sendRegistrationConfirmation(
        string $toEmail,
        string $userName,
        string $confirmUrl
    ): void {
        $subject = 'Подтверждение регистрации на Fast Food Restaurant';
        $params = array(
            'userName' => $userName,
            'confirmUrl' => $confirmUrl
        );
        $mjml = $this->prepareMjml($params);
        $html = $this->templateFacade->compileMjmlToHtml($mjml);
        $alt = "Здравствуйте, $userName! Перейдите по ссылке для подтверждения регистрации: $confirmUrl";
        $fromEmail = 'Food Restaurant@fastfoodrestaurant.ru';
        $fromName  = 'FastFood Restaurant';
        $this->sendMail($toEmail, $subject, $html, $fromEmail, $fromName, $alt);
    }

    /**
     * Отправляет уведомление о входе в аккаунт.
     *
     * @param string $toEmail Email получателя
     * @param string $userName Имя пользователя
     * @param int $unixTime
     * @throws MailException
     * @throws Exception
     */
    public function sendLoginNotification(
        string $toEmail,
        string $userName,
        int $unixTime
    ): void {
        $subject   = 'Уведомление о входе в ваш аккаунт на Fast Food Restaurant';
        $loginDate = date('d.m.Y H:i:s', $unixTime);
        $params = array(
            'userName' => $userName,
            'confirmUrl' => $loginDate
        );
        $mjml      = $this->prepareMjml($params);
        $html      = $this->templateFacade->compileMjmlToHtml($mjml);
        $alt       = "Здравствуйте, $userName! Вы вошли в аккаунт $loginDate.";
        $fromEmail = 'Food Restaurant@fastfoodrestaurant.ru';
        $fromName  = 'FastFood Restaurant';
        // $this->sendMail($toEmail, $subject, $html, $fromEmail, $fromName, $alt);
    }

    /**
     * @throws Exception
     */
    private function prepareMjml(array $params): string
    {
        $mjmlFilePath = __TEMPLATES__ . '/email' . '/confirm.mjml';
        return $this->templateFacade->render($mjmlFilePath, $params);
    }

    /**
     * Универсальный метод отправки HTML-письма через PHPMailer.
     *
     * @param string $toEmail  Email получателя
     * @param string $subject  Тема письма
     * @param string $htmlBody HTML-содержимое
     * @param string $altBody  Альтернативный текст
     *
     * @throws MailException
     */
    private function sendMail(
        string $toEmail,
        string $subject,
        string $htmlBody,
        string $fromEmail,
        string $fromName,
        string $altBody = ''
    ): void {
        try {
            $m = $this->mailer;
            $m->clearAllRecipients();
            $m->isHTML();
            $m->setFrom($fromEmail, $fromName);
            $m->addAddress($toEmail);
            $m->Subject = $subject;
            $m->Body    = $htmlBody;
            $m->AltBody = $altBody;
            $m->send();
        } catch (MailException $e) {
            Logger::error("Mail send failed to $toEmail: " . $e->getMessage());
            throw $e;
        }
    }
}
