<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $orderNumber;

    public string $customerName;

    public float $orderTotal;

    public array $items;

    public string $shippingName;

    public string $shippingAddress;

    public ?string $shippingAddress2;

    public string $shippingCity;

    public string $shippingPostcode;

    public string $shippingCountry;

    public string $shippingEmail;

    public ?string $shippingPhone;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $orderNumber,
        string $customerName,
        float $orderTotal,
        array $items,
        string $shippingName,
        string $shippingAddress,
        ?string $shippingAddress2,
        string $shippingCity,
        string $shippingPostcode,
        string $shippingCountry,
        string $shippingEmail,
        ?string $shippingPhone
    ) {
        $this->orderNumber = $orderNumber;
        $this->customerName = $customerName;
        $this->orderTotal = $orderTotal;
        $this->items = $items;
        $this->shippingName = $shippingName;
        $this->shippingAddress = $shippingAddress;
        $this->shippingAddress2 = $shippingAddress2;
        $this->shippingCity = $shippingCity;
        $this->shippingPostcode = $shippingPostcode;
        $this->shippingCountry = $shippingCountry;
        $this->shippingEmail = $shippingEmail;
        $this->shippingPhone = $shippingPhone;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Order Confirmation - #'.$this->orderNumber)
            ->view('emails.order-confirmation')
            ->with([
                'orderNumber' => $this->orderNumber,
                'customerName' => $this->customerName,
                'orderTotal' => $this->orderTotal,
                'items' => $this->items,
                'shippingName' => $this->shippingName,
                'shippingAddress' => $this->shippingAddress,
                'shippingAddress2' => $this->shippingAddress2,
                'shippingCity' => $this->shippingCity,
                'shippingPostcode' => $this->shippingPostcode,
                'shippingCountry' => $this->shippingCountry,
                'shippingEmail' => $this->shippingEmail,
                'shippingPhone' => $this->shippingPhone,
            ]);
    }
}
