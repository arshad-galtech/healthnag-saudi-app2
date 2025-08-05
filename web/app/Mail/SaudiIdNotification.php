<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SaudiIdNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $orderData;
    public $uploadRecord;
    public $attachmentPath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($orderData, $uploadRecord = null, $attachmentPath = null)
    {
        $this->orderData = $orderData;
        $this->uploadRecord = $uploadRecord;
        $this->attachmentPath = $attachmentPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->to('arshad.galtech@gmail.com')
                      ->subject('Saudi ID Upload - Order #' . $this->orderData['order_number'])
                      ->view('emails.saudi-id-notification')
                      ->with([
                          'orderData' => $this->orderData,
                          'uploadRecord' => $this->uploadRecord
                      ]);

        // Attach the ID file if available
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $filename = 'saudi_id_' . $this->orderData['order_number'];
            
            if ($this->uploadRecord) {
                $extension = pathinfo($this->uploadRecord->original_filename, PATHINFO_EXTENSION);
                $filename .= '.' . $extension;
                $mimeType = $this->uploadRecord->mime_type ?? 'application/octet-stream';
            } else {
                $extension = pathinfo($this->attachmentPath, PATHINFO_EXTENSION);
                $filename .= '.' . $extension;
                $mimeType = 'application/octet-stream';
            }

            $email->attach($this->attachmentPath, [
                'as' => $filename,
                'mime' => $mimeType
            ]);
        }

        return $email;
    }
}