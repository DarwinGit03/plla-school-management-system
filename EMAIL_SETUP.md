# Email setup

Forgot-password codes and emailed payslips use the same SMTP account.

1. Copy `.env.example` to `.env` in the project root (`C:\xampp\htdocs\plla\.env`).
2. Replace the example host, username, password, and sender with values from the email provider.
3. Use `SMTP_ENCRYPTION="ssl"` with port `465`, or `SMTP_ENCRYPTION="tls"` with port `587`, according to the provider's instructions.
4. Make sure `SMTP_FROM_EMAIL` is a sender address authorized by that provider.
5. Retry forgot password or send a payslip. If the settings are missing or invalid, the app now reports which settings need attention.

The `.env` file is ignored by Git and blocked from direct web access. Do not put real passwords in `.env.example` or commit `.env`.

For Gmail or another hosted mailbox, use the SMTP credentials or app password supported by the account and provider. A normal mailbox password may not be accepted.
