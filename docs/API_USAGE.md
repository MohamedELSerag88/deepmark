# DeepMark API – Concepts & Usage

## Brand terminology

- **Brand name**  
  A short, memorable name for the brand (e.g. “Acme”, “Stripe”).  
  - **Endpoints:** `POST /api/mobile/v1/brand-names` (generate), `POST /api/mobile/v1/brand-names/edit`, `GET/POST /api/mobile/v1/brand-names/favorites`, `POST /api/mobile/v1/brand-names/share`.  
  - **Filter favorites:** `GET /api/mobile/v1/brand-names/favorites?name=...&archetype=...` (optional query params).

- **Brand text**  
  Copy and strategy generated from the questionnaire: taglines, mission, description, colors, typography, imagery, layout (in one or both languages).  
  - **Endpoints:** `POST /api/mobile/v1/brand-text` (generate), `GET /api/mobile/v1/brand-text/history`, `POST /api/mobile/v1/brand-text/edit`.

- **Brand history**  
  Past AI generations stored per user.  
  - **Endpoint:** `GET /api/mobile/v1/brand-text/history` returns the user’s brand-text (and related) history.

---

## Register

- **Endpoint:** `POST /api/mobile/v1/register`  
- **Where to use:**  
  - Mobile app: sign-up screen.  
  - Web app: registration form.  
- **Body:** `name`, `email`, `phone`, `password`.  
- **Success:** JSON with user + token. **Errors:** Validation and other errors are now returned as JSON (with `status`, `message`, and optionally `errors`) instead of HTML.

---

## Meetings

- **Endpoints:**  
  - `GET /api/mobile/v1/meetings` – list current user’s meeting requests.  
  - `POST /api/mobile/v1/meetings` – create a meeting request (linked to a brand chat).  
- **Where to use:**  
  - After the user has at least one brand (brand names or brand text) they can request a call/meeting (e.g. “Book a strategy call”).  
  - App: “Request meeting” / “Schedule call” flow; pass `brand_id` (a `brand_chats.id`) and `meeting_at` (or `date` + `time`).  
- **Errors:** Validation and other errors are returned as JSON.

---

## Check domains & reserve domain

- **Check domains:**  
  - `POST /api/mobile/v1/brand-text/domains`  
  - **Body:** `name`, `tlds` (optional).  
  - **Where to use:** When the user enters a brand name and you want to show which TLDs are available (e.g. before “Reserve domain” or next to generated names).

- **Reserve domain:**  
  - `POST /api/mobile/v1/brand-text/reserve-domain`  
  - **Body:** `domain`, `years`, `whois_guard`, `registrant` (contact details).  
  - **Where to use:** Checkout / “Buy this domain” after the user has chosen a domain from check results (or from brand names).  
  - **Config:** Set `NAMECHEAP_API_USER`, `NAMECHEAP_API_KEY`, `NAMECHEAP_USERNAME` (and optionally `NAMECHEAP_CLIENT_IP`) in `.env`. If these are missing, the API returns a clear JSON error instead of “Parameter APIUser is missing”.

---

## Admin

- **List questions (for testing):**  
  - `GET /api/admin/questions` (with admin auth).  
  - Same response shape as mobile `GET /api/mobile/v1/questions`, so you can test the list-questions response from the admin panel.
