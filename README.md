# GenAI Web Platform

> A full‑stack, multi‑module portal built on CodeIgniter 4. It combines a professional portfolio with AI services, cryptocurrency analytics, payment processing, and an administrative suite.

## 🎯 Overview

GenAI Web Platform is the flagship web application powering [afrikenkid.com](https://afrikenkid.com). It provides a secure, scalable foundation for:

- **User authentication & account management**
- **AI-powered conversations** via Google Gemini (with memory)
- **Cryptocurrency balance lookups** (BTC, LTC)
- **Payment gateway** integration (Paystack: M‑Pesa, Airtel, cards)
- **Content management**: blog, affiliate system, admin dashboard

Whether you need a personal portfolio or a commercial SaaS product, this platform offers a ready‑made, well‑architected starting point.

## 🏗️ Architecture

Built with **CodeIgniter 4** using a modular approach:

- `app/Modules/` — self‑contained features (Portfolio, AI, Crypto, Payments, Admin, Blog, Affiliate)
- Service‑layer design for clean separation of concerns
- Environment‑based configuration (`.env`) for security
- Comprehensive documentation in `documentation.md`

## ⚡ Quick Start

### Automated (Ubuntu)

```bash
git clone https://github.com/nehemiaobati/genaiwebapplication.git
cd genaiwebapplication
sudo ./setup.sh   # installs LAMP/LEMP and configures the server
```

After setup, edit `.env` to add your API keys (Gemini, Paystack, etc.).

### Manual (any OS)

```bash
composer install
cp env .env
# Edit .env with your credentials
./spark serve
```

Visit `http://localhost:8000` and register the first admin user.

## 🔐 Security

- CSRF protection enabled
- Password hashing with `password_hash()`
- Input validation & filtering
- reCAPTCHA on public forms
- Secure session handling
- environment‑specific configs

**Important**: Never commit your real `.env` file. The repository includes an `env` example.

## 📖 Documentation

The full technical guide is in `documentation.md`. It covers:

1. Introduction & technology stack
2. Installation (automated & manual)
3. Core concepts: MVC, Service Container, Security
4. Feature deep‑dives: Auth, Payments, AI, Crypto
5. Deployment & maintenance

## 🌐 Live Demo

Explore the live site: [afrikenkid.com](https://afrikenkid.com)

## 📄 License

MIT License. See `LICENSE` file.

---

*Built with care in Nairobi, Kenya.*
