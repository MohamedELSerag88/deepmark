<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BrandingFollowUpMail extends Mailable
{
	use Queueable;
	use SerializesModels;

	public function __construct(
		public readonly string $firstName,
		public readonly string $calendlyUrl,
	) {}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: "You've Found Promising Brand Names. What's Next?",
		);
	}

	public function content(): Content
	{
		return new Content(
			htmlString: $this->htmlBody(),
		);
	}

	private function htmlBody(): string
	{
		$name = e($this->firstName !== '' ? $this->firstName : 'there');
		$link = e($this->calendlyUrl);

		return <<<HTML
<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
  <p>Hi {$name},</p>
  <p>Great progress!</p>
  <p>You’ve already shortlisted some promising brand names for your business. Finding the right name is one of the hardest parts of building a brand, and you're closer than ever to launching with confidence.</p>
  <p>But a strong brand is more than just a name.</p>
  <p>Our branding team can help you transform your favorite name into a complete, professional brand identity that stands out in the market.</p>
  <p><strong>Our branding services include:</strong></p>
  <ul>
    <li>Brand Strategy &amp; Positioning</li>
    <li>Logo Design</li>
    <li>Visual Identity System</li>
    <li>Color Palette &amp; Typography Selection</li>
    <li>Brand Guidelines</li>
    <li>Social Media Branding Assets</li>
    <li>Business Card &amp; Marketing Materials</li>
    <li>Website Visual Direction</li>
  </ul>
  <p>Whether you're still deciding between your favorite names or you're ready to move forward with one, we're here to help.</p>
  <p><strong>Ready to build your brand?</strong></p>
  <p><a href="{$link}">Book a Branding Consultation</a></p>
  <p>Best Regards,<br>The Branding Team</p>
</body>
</html>
HTML;
	}
}
