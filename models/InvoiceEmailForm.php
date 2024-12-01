<?php

namespace app\models;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Yii;
use yii\base\Model;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmailAttachment;
use Brevo\Client\Model\SendSmtpEmail;
use Symphony\Component\Mime\Address;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Transport;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user This property is read-only.
 * @property string $timesheet_csv_path
 * @property string $invoice_pdf_path
 * @property string $id_invoice
 * @property string $from_name
 * @property string $from_email
 * @property string $to_name
 * @property string $to_email
 * @property array $tasks
 * @property string $email_subject
 * @property string $invoice_month
 */
class InvoiceEmailForm extends Model
{
    public $to_email;
    public $to_name;
    public $from_email;
    public $from_name;
    public $email_subject;
    public $id_invoice;
    public $invoice_month;

    public $invoice_pdf_path;

    public $timesheet_csv_path;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['to_email', 'to_name', 'from_email', 'from_name', 'id_invoice', 'invoice_pdf_path', 'timesheet_csv_path'], 'required'],
            [['email_subject','invoice_month'], 'string'],

            //[['to_email', 'from_email'], 'email',]
            [['id_invoice'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'to_email' => 'To Email',
            'to_name' => 'To Name',
            'from_email' => 'From Email',
            'from_name' => 'From Name',
            'id_invoice' => 'Invoice ID',
            'invoice_pdf_path' => 'Invoice PDF Path',
            'timesheet_csv_path' => 'Timesheet CSV Path',
        ];
    }

    public function getTasks()
    {
        if(!file_exists($this->timesheet_csv_path)) {
            return [];
        }
        if (($handle = fopen($this->timesheet_csv_path, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $tasks[] = $row;
            }
            fclose($handle);
        }
        return $tasks;
    }

    /**
     * Render the email body.
     *
     * @return string
     */
    public function renderEmailBody()
    {
        return Yii::$app->controller->renderPartial('email-template', [
            'to_name' => $this->to_name,
            'from_name' => $this->from_name,
            'id_invoice' => $this->id_invoice,
            'subject' => $this->email_subject,
            'invoice_month' => $this->invoice_month,
            'tasks' => $this->tasks,
        ]);
    }

    //send usinbg symphony email
    public function send()
    {
        if ($this->validate()) {
            // Render the email body
            $emailBody = $this->renderEmailBody();
    
            $to_address = $this->to_name . " <" . $this->to_email . ">";
            $from_address = $this->from_name . " <" . $this->from_email . ">";

            $sig_file_data = file_get_contents($this->invoice_pdf_path . '.asc');

            // Create the email object
            $email = (new Email())
                ->from($from_address)
                ->to($to_address)
                ->subject($this->email_subject)
                ->html($emailBody)
                ->attachFromPath($this->invoice_pdf_path, basename($this->invoice_pdf_path))
                ->attach($sig_file_data, basename($this->invoice_pdf_path) . '.sig.txt', 'application/pgp-signature');

                //Yii::$app->mailer->setTransport([
                //    'dsn' => $_ENV['MAILER_DSN'],
                //]);

            $mailer = new Mailer(new BrevoApiTransport($_ENV['BREVO_API']));

            //try {
                // Send the email
                return $mailer->send($email);
            /*} catch (\Exception $e) {
                return \Exception
            }*/
        }
    
    }
}
