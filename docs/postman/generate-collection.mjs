/**
 * Generates DeepMark.postman_collection.json from the API surface in routes/mobile.php.
 * Run: node docs/postman/generate-collection.mjs
 */
import { writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

const loginData = {
  fname: 'John',
  lname: 'Doe',
  email: 'user@example.com',
  phone: '201001234567',
  image: null,
  token: '<jwt>',
};

function hdr(...pairs) {
  return pairs.map(([key, value]) => ({ key, value }));
}

function jsonHdr() {
  return hdr(['Content-Type', 'application/json'], ['Accept', 'application/json']);
}

function authHdr() {
  return hdr(
    ['Authorization', 'Bearer {{token}}'],
    ['Content-Type', 'application/json'],
    ['Accept', 'application/json']
  );
}

function authGetHdr() {
  return hdr(['Authorization', 'Bearer {{token}}'], ['Accept', 'application/json']);
}

function adminHdr() {
  return hdr(
    ['Authorization', 'Bearer {{admin_token}}'],
    ['Content-Type', 'application/json'],
    ['Accept', 'application/json']
  );
}

function adminGetHdr() {
  return hdr(['Authorization', 'Bearer {{admin_token}}'], ['Accept', 'application/json']);
}

function url(path) {
  const parts = path.replace(/^\//, '').split('/');
  return {
    raw: `{{base_url}}/${path.replace(/^\//, '')}`,
    host: ['{{base_url}}'],
    path: parts,
  };
}

function urlWithQuery(path, query) {
  const u = url(path);
  u.query = query.map(([key, value]) => ({ key, value }));
  u.raw += '?' + query.map(([k, v]) => `${k}=${v}`).join('&');
  return u;
}

function resp(name, code, body, status) {
  return {
    name,
    status: status || (code === 200 ? 'OK' : code === 201 ? 'Created' : String(code)),
    code,
    _postman_previewlanguage: 'json',
    header: [{ key: 'Content-Type', value: 'application/json' }],
    body: typeof body === 'string' ? body : JSON.stringify(body, null, 2),
  };
}

function req(name, method, path, { headers = [], body, responses = [], description, query } = {}) {
  const item = {
    name,
    request: {
      method,
      header: headers.length ? headers : hdr(['Accept', 'application/json']),
      url: query ? urlWithQuery(path, query) : url(path),
    },
    response: responses,
  };
  if (description) item.request.description = description;
  if (body !== undefined) {
    item.request.body = {
      mode: 'raw',
      raw: typeof body === 'string' ? body : JSON.stringify(body, null, 2),
    };
  }
  return item;
}

const brandNameItem = {
  suggestion_index: 1,
  id: 10,
  project_id: 1,
  name: 'Zephyra',
  archetype: 'The Hero',
  domains: {
    primary: { tld: '.com', available: true, domain: 'zephyra.com' },
    list: [
      { domain: 'zephyra.com', available: true },
      { domain: 'zephyra.io', available: false },
      { domain: 'zephyra.ai', available: true },
    ],
    more_count: 2,
  },
  liked: false,
};

const brandTextData = {
  brand_text: {
    en: { taglines: ['Live smart'], mission: '...', description: '...' },
    ar: { taglines: ['...'], mission: '...', description: '...' },
  },
  colors: [
    {
      name: { en: 'Primary', ar: 'الأساسي' },
      hex: '#112233',
      usage: { en: 'Buttons', ar: 'الأزرار' },
    },
  ],
  design_details: {
    en: {
      typography: [{ family: 'Inter', weights: ['400', '700'], usage: 'Headings' }],
      imagery: '...',
      layout: '...',
    },
    ar: {
      typography: [{ family: 'Cairo', weights: ['400', '700'], usage: 'العناوين' }],
      imagery: '...',
      layout: '...',
    },
  },
};

const siteSettings = {
  brand_name: 'DeepMark',
  logo_url: 'https://example.com/logo.png',
  login_cta_label: 'Log in',
  login_cta_url: '/login',
  start_cta_label: 'Start branding',
  start_cta_url: '/start',
  footer_tagline: 'Brand with confidence',
  footer_copyright: '© DeepMark',
  newsletter_placeholder: 'Your email',
  social_links: { twitter: 'https://x.com/deepmark' },
  contact_email: 'hello@deepmark.com',
  blogs_title: 'Insights',
};

const blogPost = {
  id: 'brand-strategy-101',
  slug: 'brand-strategy-101',
  date: '2025-11-01',
  published_at: '2025-11-01T10:00:00Z',
  title: 'Brand Strategy 101',
  badge: 'Guide',
  image: 'https://example.com/blog.jpg',
  author: { name: 'Alex', title: 'Strategist', avatar: null },
  lead: 'Start here.',
  content: '<p>...</p>',
};

const marketingProject = {
  id: 1,
  slug: 'zephyra',
  name: 'Zephyra',
  title: 'Zephyra',
  year: 2025,
  description: 'A calm productivity brand',
  archetype: 'The Sage',
  domains: brandNameItem.domains,
  liked: false,
  image: 'https://example.com/project.jpg',
  image_alt: 'Zephyra',
  author: { name: 'Team', position: 'Studio', avatar: null },
  detail: { lead: '...', images: [], content: [], deliverables: [] },
};

const collection = {
  info: {
    name: 'DeepMark API',
    _postman_id: '3d7c2d3b-9fd0-48c4-9a3b-5e7c0d7c0d3a',
    description:
      'Full Postman collection extracted from DeepMark (routes/mobile.php).\n\n' +
      'Base path: {{base_url}}/api\n' +
      'Mobile auth: Authorization: Bearer {{token}}\n' +
      'Admin auth: Authorization: Bearer {{admin_token}}\n\n' +
      'Generated from controllers + validation rules.',
    schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
  },
  item: [
    // ─── Social Auth ───────────────────────────────────────────
    {
      name: 'Social Auth',
      item: [
        req('Redirect to Provider', 'GET', 'api/auth/google/redirect', {
          responses: [
            resp('Success', 200, { redirect_url: 'https://accounts.google.com/o/oauth2/...' }),
            resp('Invalid provider', 400, { message: 'Invalid provider' }),
          ],
        }),
        req('OAuth Callback', 'GET', 'api/auth/google/callback', {
          description: 'OAuth callback (browser redirect). Returns LoginResource.',
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: loginData,
              message: 'user founded successfully',
            }),
            resp('Failed', 401, { status: 'error', message: 'Authentication failed: ...' }),
          ],
        }),
        req('Exchange Social Token', 'POST', 'api/auth/google/token', {
          headers: jsonHdr(),
          body: { access_token: '<provider_access_token>' },
          responses: [
            resp('Success', 200, {
              status: 'success',
              user: { id: 1, email: 'user@example.com' },
              token: '<plainTextToken>',
              token_type: 'Bearer',
            }),
            resp('Failed', 401, { status: 'error', message: 'Authentication failed: ...' }),
          ],
        }),
      ],
    },

    // ─── Mobile Auth ───────────────────────────────────────────
    {
      name: 'Auth',
      item: [
        req('Login (phone/password)', 'POST', 'api/mobile/v1/login', {
          headers: jsonHdr(),
          body: { phone: '201001234567', password: 'StrongPass123' },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: loginData,
              message: 'user founded successfully',
            }),
            resp('Wrong credentials', 200, {
              status: 'FAILED',
              message: 'wrong credentials',
            }),
          ],
        }),
        req('Send OTP (email)', 'POST', 'api/mobile/v1/send-otp', {
          headers: jsonHdr(),
          body: { email: 'user@example.com' },
          responses: [
            resp('Success', 200, { status: 'OK', message: 'SMS Sent with otp' }),
            resp('Validation error', 422, {
              status: 'FAILED',
              message: 'The email field is required.',
              errors: { email: ['The email field is required.'] },
            }),
          ],
        }),
        req('Check OTP (login)', 'POST', 'api/mobile/v1/check-otp', {
          headers: jsonHdr(),
          body: {
            email: 'user@example.com',
            otp_code: '123456',
            device_token: 'optional-guest-device-token',
          },
          description: 'On success, copy data.token into {{token}}.',
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: loginData,
              message: 'logged in successfully.',
            }),
            resp('Wrong OTP', 200, { status: 'FAILED', message: 'Wrong Otp token!' }),
          ],
        }),
        req('Forget Password', 'POST', 'api/mobile/v1/forget-password', {
          headers: jsonHdr(),
          body: { phone: '201001234567' },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              message: 'SMS Sent with otp',
              token: 1234,
            }),
          ],
        }),
        req('Reset Password', 'POST', 'api/mobile/v1/reset-password', {
          headers: jsonHdr(),
          body: {
            reset_password: '1234',
            new_password: 'StrongPass123',
            new_password_confirmation: 'StrongPass123',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: loginData,
              message: 'password updated successfully',
            }),
            resp('Wrong code', 200, {
              status: 'FAILED',
              message: 'wrong reset password code',
            }),
          ],
        }),
        req('Register', 'POST', 'api/mobile/v1/register', {
          headers: jsonHdr(),
          body: {
            name: 'John Doe',
            email: 'john@example.com',
            phone: '201001234567',
            password: 'StrongPass123',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: { ...loginData, email: 'john@example.com' },
              message: 'user created successfully',
            }),
            resp('Validation error', 422, {
              status: 'FAILED',
              message: 'The email has already been taken.',
              errors: { email: ['The email has already been taken.'] },
            }),
          ],
        }),
        req('Social Login', 'POST', 'api/mobile/v1/social-login', {
          headers: jsonHdr(),
          body: {
            provider: 'google',
            token: '<id_token>',
            email: 'olivia@example.com',
            fname: 'Olivia',
            lname: 'Ham',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                fname: 'Olivia',
                lname: 'Ham',
                email: 'olivia@example.com',
                phone: null,
                image: null,
                token: '<jwt>',
              },
              message: 'logged in successfully.',
            }),
            resp('Invalid token', 401, {
              status: 'FAILED',
              message: 'Invalid social token',
            }),
          ],
        }),
      ],
    },

    // ─── Marketing (public) ────────────────────────────────────
    {
      name: 'Marketing (Public)',
      item: [
        req('Home (aggregated)', 'GET', 'api/mobile/v1/marketing/home', {
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                settings: siteSettings,
                sections: {
                  hero: { key: 'hero', content: { headline: 'Build your brand' }, sort_order: 0 },
                },
                projects: [marketingProject],
                faqs: [{ id: 1, question: 'What is DeepMark?', answer: '...', sort_order: 0 }],
                pricing: [
                  {
                    id: 1,
                    slug: 'starter',
                    name: 'Starter',
                    price_display: 'Free',
                    currency_symbol: '$',
                    description: 'For individuals',
                    features: ['3 generations / month'],
                    badge: null,
                    is_recommended: false,
                    cta_label: 'Start',
                    cta_url: '/start',
                    sort_order: 0,
                  },
                ],
              },
            }),
          ],
        }),
        req('Site Settings', 'GET', 'api/mobile/v1/marketing/settings', {
          responses: [resp('Success', 200, { status: 'OK', data: siteSettings })],
        }),
        req('List Portfolio Projects', 'GET', 'api/mobile/v1/marketing/projects', {
          responses: [resp('Success', 200, { status: 'OK', data: [marketingProject] })],
        }),
        req('Show Portfolio Project', 'GET', 'api/mobile/v1/marketing/projects/1', {
          responses: [
            resp('Success', 200, { status: 'OK', data: marketingProject }),
            resp('Not found', 404, { message: 'Project not found' }),
          ],
        }),
        req('List Blogs', 'GET', 'api/mobile/v1/marketing/blogs', {
          responses: [resp('Success', 200, { status: 'OK', data: [blogPost] })],
        }),
        req('Show Blog by Slug', 'GET', 'api/mobile/v1/marketing/blogs/brand-strategy-101', {
          responses: [
            resp('Success', 200, { status: 'OK', data: blogPost }),
            resp('Not found', 404, { message: 'Blog post not found' }),
          ],
        }),
        req('List FAQs', 'GET', 'api/mobile/v1/marketing/faqs', {
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: [{ id: 1, question: 'What is DeepMark?', answer: '...', sort_order: 0 }],
            }),
          ],
        }),
        req('List Pricing', 'GET', 'api/mobile/v1/marketing/pricing', {
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: [
                {
                  id: 1,
                  slug: 'pro',
                  name: 'Pro',
                  price_display: '19.99',
                  currency_symbol: '$',
                  description: 'For teams',
                  features: ['Unlimited generations'],
                  badge: 'Popular',
                  is_recommended: true,
                  cta_label: 'Subscribe',
                  cta_url: '/subscribe',
                  sort_order: 1,
                },
              ],
            }),
          ],
        }),
        req('Contact Submit', 'POST', 'api/mobile/v1/marketing/contact', {
          headers: jsonHdr(),
          body: {
            name: 'Jane Doe',
            email: 'jane@example.com',
            brand: 'Acme',
            description: 'Need a full brand identity',
            budget: '5k-10k',
            timeline: '1-2 months',
          },
          responses: [
            resp(
              'Success',
              201,
              {
                status: 'OK',
                message: 'Thank you. We will get back to you soon.',
                submission: {
                  id: 1,
                  name: 'Jane Doe',
                  email: 'jane@example.com',
                  brand: 'Acme',
                  is_read: false,
                },
              },
              'Created'
            ),
          ],
        }),
      ],
    },

    // ─── Questions ─────────────────────────────────────────────
    {
      name: 'Questions',
      item: [
        req('List Questions', 'GET', 'api/mobile/v1/questions', {
          description: 'Public — no auth required.',
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: [
                {
                  id: 1,
                  question_en: "What's your brand's main point-of-view or core belief?",
                  question_ar: 'ما هو الموقف أو الاعتقاد الأساسي لعلامتك؟',
                  question_type: 'text',
                  answers: null,
                  description_en: 'Strong brands stand for something.',
                  description_ar: 'العلامات القوية تؤمن بشيء ما.',
                  video_url: null,
                  video_path: null,
                  image_url: null,
                  example_answer: 'We believe great design should be accessible.',
                  why_matters: null,
                  resources: [{ url: 'https://example.com', text: 'Why it matters' }],
                },
              ],
            }),
          ],
        }),
      ],
    },

    // ─── Brand Names ───────────────────────────────────────────
    {
      name: 'Brand Names',
      item: [
        req('Generate Brand Names', 'POST', 'api/mobile/v1/brand-names', {
          headers: jsonHdr(),
          description: 'Public (optional auth). Guest chats can pass device_token.',
          body: {
            language: 'en',
            count: 12,
            tlds: ['com', 'io', 'ai'],
            device_token: 'optional-guest-device-token',
            answers: [
              { question_id: 1, value: 'We believe great design should be accessible' },
              { question_id: 2, value: 'Ambitious founders' },
            ],
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                id: 1,
                project_id: 1,
                chat_id: 1,
                response_message: 'I generated brand name suggestions for you.',
                items: [brandNameItem],
                payload: {
                  response_message: 'I generated brand name suggestions for you.',
                  items: [brandNameItem],
                },
              },
            }),
            resp('Failed', 500, {
              status: 'FAILED',
              message: 'Failed to generate brand names.',
              error: '...',
            }),
          ],
        }),
        req('Edit Brand Names (Chat)', 'POST', 'api/mobile/v1/brand-names/edit', {
          headers: authHdr(),
          body: {
            chat_id: 1,
            comment: 'Make names shorter, more energetic',
            tlds: ['com', 'io', 'ai'],
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                id: 2,
                project_id: 1,
                chat_id: 1,
                items: [{ ...brandNameItem, name: 'Vanguard' }],
              },
            }),
            resp('Not found', 404, { status: 'FAILED', message: 'Chat not found' }),
          ],
        }),
        req('List Favorites', 'GET', 'api/mobile/v1/brand-names/favorites', {
          headers: authGetHdr(),
          query: [
            ['brand_chat_id', '1'],
            ['name', ''],
            ['archetype', ''],
          ],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                items: [
                  {
                    id: 1,
                    brand_chat_id: 1,
                    brand_name_suggestion_id: 10,
                    created_at: '2025-11-30T10:00:00Z',
                    suggestion: brandNameItem,
                  },
                ],
              },
            }),
          ],
        }),
        req('Save Favorite', 'POST', 'api/mobile/v1/brand-names/favorites', {
          headers: authHdr(),
          body: { project_id: 1, suggestion_id: 10 },
          responses: [
            resp('Success', 200, { status: 'OK', data: { id: 1 } }),
            resp('Not found', 404, { message: 'Project not found' }),
          ],
        }),
        req('Remove Favorite', 'DELETE', 'api/mobile/v1/brand-names/favorites/1', {
          headers: authGetHdr(),
          responses: [
            resp('Success', 200, { status: 'OK', message: 'Removed from favorites' }),
            resp('Not found', 404, { message: 'Favorite not found' }),
          ],
        }),
        req('Share Brand Names', 'POST', 'api/mobile/v1/brand-names/share', {
          headers: authHdr(),
          body: {
            emails: ['friend@example.com'],
            subject: 'Check these name ideas',
            message: 'What do you think?',
            brand_chat_id: 1,
            names: [{ name: 'Zephyra', archetype: 'The Hero' }],
          },
          responses: [resp('Success', 200, { status: 'OK', message: 'Shared successfully' })],
        }),
      ],
    },

    // ─── Brand Text ────────────────────────────────────────────
    {
      name: 'Brand Text',
      item: [
        req('Generate Brand Text', 'POST', 'api/mobile/v1/brand-text', {
          headers: authHdr(),
          body: {
            language: 'both',
            answers: [
              { question_id: 1, value: 'Apartment' },
              { question_id: 2, value: ['Pool', 'Gym'] },
            ],
          },
          responses: [
            resp('Success', 200, { status: 'OK', data: brandTextData }),
            resp('Fallback (raw)', 200, {
              status: 'OK',
              data: { brand_text: null, colors: [], design_details: [], raw: 'Unstructured...' },
            }),
          ],
        }),
        req('Brand Text History', 'GET', 'api/mobile/v1/brand-text/history', {
          headers: authGetHdr(),
          query: [['q', '']],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: [
                {
                  id: 1,
                  language: 'en',
                  answers: [],
                  response: brandTextData,
                  raw_response: null,
                  created_at: '2025-11-30T10:00:00Z',
                },
              ],
            }),
          ],
        }),
        req('Edit Brand Text', 'POST', 'api/mobile/v1/brand-text/edit', {
          headers: authHdr(),
          body: {
            chat_id: 1,
            comment: 'Make taglines shorter and more premium',
            language: 'en',
          },
          responses: [resp('Success', 200, { status: 'OK', data: brandTextData })],
        }),
        req('Check Domains', 'POST', 'api/mobile/v1/brand-text/domains', {
          headers: authHdr(),
          body: { name: 'DeepMark', tlds: ['com', 'io', 'ai'] },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                results: [
                  { domain: 'deepmark.com', available: true },
                  { domain: 'deepmark.io', available: false },
                ],
              },
            }),
          ],
        }),
        req('Reserve Domain', 'POST', 'api/mobile/v1/brand-text/reserve-domain', {
          headers: authHdr(),
          body: {
            domain: 'deepmark.io',
            years: 1,
            whois_guard: false,
            registrant: {
              first_name: 'John',
              last_name: 'Doe',
              address1: '123 Main St',
              city: 'Cairo',
              state_province: 'Cairo',
              postal_code: '12345',
              country: 'EG',
              phone: '+20.1000000000',
              email: 'john@example.com',
            },
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                reservation_id: 10,
                status: 'success',
                provider_order_id: '1234567',
              },
              message: 'Domain reserved successfully',
            }),
            resp('Failed', 400, {
              status: 'FAILED',
              reservation_id: 11,
              error: 'Order validation failed',
            }),
          ],
        }),
      ],
    },

    // ─── Invites ───────────────────────────────────────────────
    {
      name: 'Invites',
      item: [
        req('List Invitations', 'GET', 'api/mobile/v1/invites', {
          headers: authGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                items: [
                  {
                    id: 1,
                    email: 'friend@example.com',
                    status: 'pending',
                    accepted_at: null,
                    created_at: '2025-11-30T10:00:00Z',
                  },
                ],
              },
            }),
          ],
        }),
        req('Send Invites', 'POST', 'api/mobile/v1/invites', {
          headers: authHdr(),
          body: {
            emails: ['friend1@example.com', 'friend2@example.com'],
            message: 'Join me on DeepMark',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: { items: [{ id: 1, email: 'friend1@example.com' }] },
              message: 'Invitations sent',
            }),
          ],
        }),
      ],
    },

    // ─── Projects (BrandChat) ──────────────────────────────────
    {
      name: 'Projects',
      item: [
        req('List Projects', 'GET', 'api/mobile/v1/projects', {
          headers: authGetHdr(),
          query: [
            ['topic', 'brand_names'],
            ['per_page', '10'],
            ['page', '1'],
          ],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              projects: [
                {
                  id: 1,
                  project_id: 1,
                  chat_id: 1,
                  parent_id: null,
                  topic: 'brand_names',
                  language: 'en',
                  answers: [],
                  archetype: null,
                  items: [brandNameItem],
                  raw_response: null,
                  created_at: '2025-11-30T10:00:00Z',
                  device_token: null,
                },
              ],
              pagination: { current_page: 1, per_page: 10, total: 1, last_page: 1 },
            }),
          ],
        }),
        req('Show Project', 'GET', 'api/mobile/v1/projects/1', {
          headers: authGetHdr(),
          query: [
            ['name', ''],
            ['archetype', ''],
          ],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                project: {
                  id: 1,
                  project_id: 1,
                  chat_id: 1,
                  topic: 'brand_names',
                  items: [brandNameItem],
                },
              },
            }),
            resp('Not found', 404, { message: 'Project not found' }),
          ],
        }),
        req('Get Chat History', 'GET', 'api/mobile/v1/projects/1/chat-history', {
          headers: authGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                project_id: 1,
                messages: [
                  {
                    id: 1,
                    brand_chat_id: 1,
                    user_id: 1,
                    role: 'user',
                    message: 'Make it shorter',
                    payload: null,
                    created_at: '2025-11-30T10:00:00Z',
                  },
                  {
                    id: 2,
                    brand_chat_id: 1,
                    user_id: null,
                    role: 'assistant',
                    message: 'Here are updated names',
                    payload: { items: [brandNameItem] },
                    created_at: '2025-11-30T10:00:05Z',
                  },
                ],
              },
            }),
          ],
        }),
        req('Post Chat Message', 'POST', 'api/mobile/v1/projects/1/chat-history', {
          headers: authHdr(),
          body: { message: 'Make names shorter', tlds: ['com', 'io'] },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                project_id: 1,
                user_message: { id: 1, role: 'user', message: 'Make names shorter' },
                assistant_message: { id: 2, role: 'assistant', message: 'Updated names' },
                payload: { response_message: 'Updated names', items: [brandNameItem] },
              },
              message: 'Chat updated successfully',
            }),
          ],
        }),
      ],
    },

    // ─── Meetings ──────────────────────────────────────────────
    {
      name: 'Meetings',
      item: [
        req('List My Meetings', 'GET', 'api/mobile/v1/meetings', {
          headers: authGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: [
                {
                  id: 22,
                  brand_chat_id: 1,
                  meeting_at: '2025-12-05T14:30:00Z',
                  notes: 'Discuss visual identity',
                  status: 'pending',
                  created_at: '2025-11-30T10:00:00Z',
                },
              ],
            }),
          ],
        }),
        req('Request Meeting', 'POST', 'api/mobile/v1/meetings', {
          headers: authHdr(),
          body: {
            brand_id: 1,
            date: '2026-08-05',
            time: '14:30',
            notes: 'Discuss visual identity',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                id: 22,
                brand_chat_id: 1,
                meeting_at: '2026-08-05T14:30:00Z',
                status: 'pending',
              },
              message: 'Meeting request created',
            }),
            resp('Past time', 422, {
              status: 'FAILED',
              message: 'Meeting time must be in the future',
            }),
          ],
        }),
      ],
    },

    // ─── Profile ───────────────────────────────────────────────
    {
      name: 'Profile',
      item: [
        req('Get Profile', 'GET', 'api/mobile/v1/profile', {
          headers: authGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                user: {
                  id: 1,
                  fname: 'John',
                  lname: 'Doe',
                  email: 'john@example.com',
                  phone: '201001234567',
                  image: null,
                  country: 'EG',
                  time_zone: 'Africa/Cairo',
                  bio: null,
                  name: 'John Doe',
                },
                stats: { chats_count: 3, meetings_count: 1 },
                latest: { chats: [], meetings: [] },
                todos: [{ key: 'edit_profile', label: 'Complete your profile' }],
              },
            }),
          ],
        }),
        req('Update Profile', 'POST', 'api/mobile/v1/profile', {
          headers: authHdr(),
          description: 'Also accepts multipart/form-data with image file.',
          body: {
            fname: 'John',
            lname: 'Doe',
            country: 'EG',
            time_zone: 'Africa/Cairo',
            bio: 'Founder',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                id: 1,
                fname: 'John',
                lname: 'Doe',
                email: 'john@example.com',
                phone: '201001234567',
                image: null,
                country: 'EG',
                time_zone: 'Africa/Cairo',
                bio: 'Founder',
                name: 'John Doe',
              },
              message: 'Profile updated successfully',
            }),
          ],
        }),
        req('Update Password', 'PATCH', 'api/mobile/v1/profile/password', {
          headers: authHdr(),
          body: {
            current_password: 'OldPass123',
            password: 'StrongPass123',
            password_confirmation: 'StrongPass123',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              message: 'Password updated successfully',
            }),
            resp('Wrong current password', 422, {
              status: 'FAILED',
              message: 'Current password is incorrect',
            }),
          ],
        }),
      ],
    },

    // ─── Subscriptions ─────────────────────────────────────────
    {
      name: 'Subscriptions',
      item: [
        req('List Plans', 'GET', 'api/mobile/v1/plans', {
          headers: authGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: [
                {
                  id: 1,
                  name: 'Basic',
                  description: 'Starter plan for individuals',
                  price_cents: 0,
                  currency: 'usd',
                  interval: 'month',
                  features: [
                    {
                      key: 'brand_generations',
                      label: 'Brand generations / month',
                      value: '3',
                    },
                  ],
                },
              ],
            }),
          ],
        }),
        req('Get My Subscription', 'GET', 'api/mobile/v1/subscription', {
          headers: authGetHdr(),
          responses: [
            resp('Active', 200, {
              status: 'OK',
              data: {
                status: 'active',
                plan: {
                  id: 2,
                  name: 'Pro',
                  price_cents: 1999,
                  currency: 'usd',
                  interval: 'month',
                },
                started_at: '2025-11-30T10:00:00Z',
                ends_at: null,
              },
            }),
            resp('No subscription', 200, { status: 'OK', data: null }),
          ],
        }),
        req('Subscribe', 'POST', 'api/mobile/v1/subscribe', {
          headers: authHdr(),
          body: { plan_id: 2 },
          responses: [
            resp('Free plan', 200, {
              status: 'OK',
              data: { subscription_id: 12, status: 'active' },
              message: 'Subscribed to free plan',
            }),
            resp('Paid plan (Stripe Checkout)', 200, {
              status: 'OK',
              data: {
                checkout_url: 'https://checkout.stripe.com/c/session_abc',
                session_id: 'cs_test_123',
              },
            }),
          ],
        }),
        req('Stripe Webhook', 'POST', 'api/mobile/v1/webhooks/stripe', {
          headers: hdr(
            ['Content-Type', 'application/json'],
            ['Stripe-Signature', 't=...,v1=...']
          ),
          body: { type: 'checkout.session.completed', data: { object: {} } },
          description: 'Public. Requires valid Stripe-Signature header.',
          responses: [
            {
              name: 'OK',
              status: 'OK',
              code: 200,
              body: 'OK',
            },
            {
              name: 'Invalid signature',
              status: 'Bad Request',
              code: 400,
              body: 'Invalid signature',
            },
          ],
        }),
      ],
    },

    // ─── Admin Auth ────────────────────────────────────────────
    {
      name: 'Admin / Auth',
      item: [
        req('Admin Login', 'POST', 'api/admin/user/login', {
          headers: jsonHdr(),
          body: { email: 'admin@example.com', password: 'password' },
          description: 'On success, copy access_token into {{admin_token}}.',
          responses: [
            resp('Success', 200, {
              status: 'OK',
              access_token: '<admin_jwt>',
              token_type: 'bearer',
              user: { id: 1, name: 'Admin', email: 'admin@example.com' },
            }),
            resp('Wrong credentials', 200, {
              status: 'FAILED',
              message: 'wrong credentials',
            }),
          ],
        }),
        req('Admin Profile', 'GET', 'api/admin/user/profile', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              user: { id: 1, name: 'Admin', email: 'admin@example.com' },
            }),
          ],
        }),
      ],
    },

    // ─── Admin Dashboard ───────────────────────────────────────
    {
      name: 'Admin / Dashboard',
      item: [
        req('Overview', 'GET', 'api/admin/dashboard', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: {
                users: { total: 100 },
                brands: {
                  total_chats: 50,
                  brand_names_chats: 30,
                  brand_text_chats: 20,
                  favorites_total: 40,
                },
                questions: { total: 12 },
                meetings: { total: 8, upcoming: 3, done: 5 },
                subscriptions: {
                  by_status: { active: 10, pending: 2, canceled: 1 },
                  active_amount_cents: 19990,
                  currency: 'USD',
                },
              },
            }),
          ],
        }),
      ],
    },

    // ─── Admin Users ───────────────────────────────────────────
    {
      name: 'Admin / Users',
      item: [
        req('List Users', 'GET', 'api/admin/users', {
          headers: adminGetHdr(),
          query: [
            ['q', ''],
            ['date_from', ''],
            ['date_to', ''],
            ['date_joined', 'newest'],
            ['per_page', '15'],
          ],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              users: [
                {
                  id: 1,
                  name: 'John Doe',
                  email: 'john@example.com',
                  plan: 'Paid',
                  joined_at: '2025-11-01',
                },
              ],
              pagination: { current_page: 1, per_page: 15, total: 1, last_page: 1 },
            }),
          ],
        }),
        req('Export Users (JSON link)', 'GET', 'api/admin/users/export', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              file: {
                name: 'users_export_20251130.csv',
                size_kb: 12,
                url: 'https://example.com/storage/exports/users_export.csv',
              },
            }),
          ],
        }),
        req('Export Users Download (CSV)', 'GET', 'api/admin/users/export/download', {
          headers: adminGetHdr(),
          responses: [],
        }),
        req('Show User', 'GET', 'api/admin/users/1', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              user: { id: 1, fname: 'John', lname: 'Doe', email: 'john@example.com' },
              projects: [],
              plans: [],
            }),
          ],
        }),
        req('Update User', 'PUT', 'api/admin/users/1', {
          headers: adminHdr(),
          body: {
            fname: 'John',
            lname: 'Doe',
            email: 'john@example.com',
            phone: '201001234567',
            password: null,
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              user: {
                id: 1,
                fname: 'John',
                lname: 'Doe',
                name: 'John Doe',
                email: 'john@example.com',
                phone: '201001234567',
              },
              message: 'User updated successfully',
            }),
          ],
        }),
        req('Delete User', 'DELETE', 'api/admin/users/1', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              message: 'User deleted successfully',
              id: 1,
            }),
          ],
        }),
        req('User Projects', 'GET', 'api/admin/users/1/projects', {
          headers: adminGetHdr(),
          query: [['per_page', '15']],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              projects: [],
              pagination: { current_page: 1, per_page: 15, total: 0, last_page: 1 },
            }),
          ],
        }),
      ],
    },

    // ─── Admin Brands ──────────────────────────────────────────
    {
      name: 'Admin / Brands',
      item: [
        req('List Brands', 'GET', 'api/admin/brands', {
          headers: adminGetHdr(),
          query: [['per_page', '15']],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              brands: [{ id: 1, topic: 'brand_names', user_id: 1 }],
              pagination: { current_page: 1, per_page: 15, total: 1, last_page: 1 },
            }),
          ],
        }),
        req('Show Brand', 'GET', 'api/admin/brands/1', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              brand: { id: 1, topic: 'brand_names', user_id: 1 },
            }),
            resp('Not found', 404, { message: 'Brand not found' }),
          ],
        }),
        req('Show Project (alias)', 'GET', 'api/admin/projects/1', {
          headers: adminGetHdr(),
          description: 'Alias of GET /api/admin/brands/{id}',
          responses: [
            resp('Success', 200, {
              status: 'OK',
              brand: { id: 1, topic: 'brand_names', user_id: 1 },
            }),
          ],
        }),
      ],
    },

    // ─── Admin Questions ───────────────────────────────────────
    {
      name: 'Admin / Questions',
      item: [
        req('List Questions', 'GET', 'api/admin/questions', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              data: [
                {
                  id: 1,
                  question_en: 'What is your brand POV?',
                  question_ar: null,
                  question_type: 'text',
                },
              ],
            }),
          ],
        }),
        req('Create Question', 'POST', 'api/admin/questions', {
          headers: adminHdr(),
          body: {
            question_en: 'What is your brand POV?',
            question_ar: 'ما هو موقف علامتك؟',
            question_type: 'text',
            description_en: '...',
            description_ar: '...',
            example_answer: '...',
            resources: [{ url: 'https://example.com', text: 'Guide' }],
          },
          responses: [
            resp(
              'Created',
              201,
              { status: 'OK', question: { id: 1, question_en: 'What is your brand POV?' } },
              'Created'
            ),
          ],
        }),
        req('Show Question', 'GET', 'api/admin/questions/1', {
          headers: adminGetHdr(),
          responses: [
            resp('Success', 200, {
              status: 'OK',
              question: { id: 1, question_en: 'What is your brand POV?' },
            }),
          ],
        }),
        req('Update Question', 'PUT', 'api/admin/questions/1', {
          headers: adminHdr(),
          body: {
            question_en: 'Updated question?',
            question_type: 'text',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              question: { id: 1, question_en: 'Updated question?' },
            }),
          ],
        }),
        req('Delete Question', 'DELETE', 'api/admin/questions/1', {
          headers: adminGetHdr(),
          responses: [resp('Success', 200, { status: 'OK', message: 'Deleted', id: 1 })],
        }),
      ],
    },

    // ─── Admin Meetings ────────────────────────────────────────
    {
      name: 'Admin / Meetings',
      item: [
        req('List Meetings', 'GET', 'api/admin/meetings', {
          headers: adminGetHdr(),
          query: [
            ['per_page', '15'],
            ['status', 'pending'],
            ['user_id', ''],
          ],
          responses: [
            resp('Success', 200, {
              status: 'OK',
              meetings: [
                {
                  id: 1,
                  status: 'pending',
                  meeting_at: '2026-08-05T14:30:00Z',
                  notes: null,
                  user: { id: 1, name: 'John Doe', email: 'john@example.com' },
                  brand: { id: 1, topic: 'brand_names', title: 'Zephyra' },
                  created_at: '2025-11-30T10:00:00Z',
                },
              ],
              pagination: { current_page: 1, per_page: 15, total: 1, last_page: 1 },
            }),
          ],
        }),
        req('Update Meeting', 'PUT', 'api/admin/meetings/1', {
          headers: adminHdr(),
          body: {
            status: 'approved',
            meeting_at: '2026-08-05 14:30:00',
            notes: 'Confirmed',
          },
          responses: [
            resp('Success', 200, {
              status: 'OK',
              meeting: { id: 1, status: 'approved' },
            }),
          ],
        }),
      ],
    },

    // ─── Admin Marketing ───────────────────────────────────────
    {
      name: 'Admin / Marketing',
      item: [
        {
          name: 'Settings',
          item: [
            req('Get Settings', 'GET', 'api/admin/marketing/settings', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', settings: siteSettings })],
            }),
            req('Update Settings', 'PUT', 'api/admin/marketing/settings', {
              headers: adminHdr(),
              body: {
                brand_name_en: 'DeepMark',
                brand_name_ar: 'ديب مارك',
                logo_url: 'https://example.com/logo.png',
                login_cta_label_en: 'Log in',
                start_cta_label_en: 'Start branding',
                footer_tagline_en: 'Brand with confidence',
                contact_email: 'hello@deepmark.com',
                social_links: { twitter: 'https://x.com/deepmark' },
              },
              responses: [resp('Success', 200, { status: 'OK', settings: siteSettings })],
            }),
          ],
        },
        {
          name: 'Home Sections',
          item: [
            req('List', 'GET', 'api/admin/marketing/home-sections', {
              headers: adminGetHdr(),
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  sections: [{ id: 1, section_key: 'hero', is_active: true }],
                }),
              ],
            }),
            req('Create', 'POST', 'api/admin/marketing/home-sections', {
              headers: adminHdr(),
              body: {
                section_key: 'hero',
                content_en: { headline: 'Build your brand' },
                content_ar: { headline: 'ابنِ علامتك' },
                is_active: true,
                sort_order: 0,
              },
              responses: [
                resp(
                  'Created',
                  201,
                  { status: 'OK', section: { id: 1, section_key: 'hero' } },
                  'Created'
                ),
              ],
            }),
            req('Show', 'GET', 'api/admin/marketing/home-sections/1', {
              headers: adminGetHdr(),
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  section: { id: 1, section_key: 'hero' },
                }),
              ],
            }),
            req('Update', 'PUT', 'api/admin/marketing/home-sections/1', {
              headers: adminHdr(),
              body: {
                content_en: { headline: 'Updated' },
                is_active: true,
                sort_order: 1,
              },
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  section: { id: 1, section_key: 'hero' },
                }),
              ],
            }),
            req('Delete', 'DELETE', 'api/admin/marketing/home-sections/1', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', message: 'Deleted', id: 1 })],
            }),
          ],
        },
        {
          name: 'Portfolio Projects',
          item: [
            req('List', 'GET', 'api/admin/marketing/projects', {
              headers: adminGetHdr(),
              query: [['featured_only', 'true']],
              responses: [resp('Success', 200, { status: 'OK', projects: [marketingProject] })],
            }),
            req('Show', 'GET', 'api/admin/marketing/projects/1', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', project: marketingProject })],
            }),
            req('Update Marketing Fields', 'PUT', 'api/admin/marketing/projects/1', {
              headers: adminHdr(),
              body: {
                is_marketing_featured: true,
                marketing_image_url: 'https://example.com/project.jpg',
                marketing_description_en: 'A calm productivity brand',
                marketing_description_ar: '...',
                marketing_lead_en: 'Lead text',
                marketing_author_name: 'Team',
                marketing_author_position: 'Studio',
              },
              responses: [resp('Success', 200, { status: 'OK', project: marketingProject })],
            }),
          ],
        },
        {
          name: 'Blogs',
          item: [
            req('List', 'GET', 'api/admin/marketing/blogs', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', posts: [blogPost] })],
            }),
            req('Create', 'POST', 'api/admin/marketing/blogs', {
              headers: adminHdr(),
              body: {
                slug: 'brand-strategy-101',
                title_en: 'Brand Strategy 101',
                title_ar: 'استراتيجية العلامة',
                badge_en: 'Guide',
                lead_en: 'Start here.',
                content_en: '<p>...</p>',
                published_at: '2025-11-01 10:00:00',
                image_url: 'https://example.com/blog.jpg',
                author_name: 'Alex',
                author_title: 'Strategist',
                is_active: true,
                sort_order: 0,
              },
              responses: [
                resp('Created', 201, { status: 'OK', post: blogPost }, 'Created'),
              ],
            }),
            req('Show', 'GET', 'api/admin/marketing/blogs/1', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', post: blogPost })],
            }),
            req('Update', 'PUT', 'api/admin/marketing/blogs/1', {
              headers: adminHdr(),
              body: { title_en: 'Updated title', is_active: true },
              responses: [resp('Success', 200, { status: 'OK', post: blogPost })],
            }),
            req('Delete', 'DELETE', 'api/admin/marketing/blogs/1', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', message: 'Deleted', id: 1 })],
            }),
          ],
        },
        {
          name: 'FAQs',
          item: [
            req('List', 'GET', 'api/admin/marketing/faqs', {
              headers: adminGetHdr(),
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  faqs: [{ id: 1, question_en: 'What is DeepMark?', answer_en: '...' }],
                }),
              ],
            }),
            req('Create', 'POST', 'api/admin/marketing/faqs', {
              headers: adminHdr(),
              body: {
                question_en: 'What is DeepMark?',
                answer_en: 'A branding platform.',
                question_ar: 'ما هو ديب مارك؟',
                answer_ar: '...',
                is_active: true,
                sort_order: 0,
              },
              responses: [
                resp(
                  'Created',
                  201,
                  { status: 'OK', faq: { id: 1, question_en: 'What is DeepMark?' } },
                  'Created'
                ),
              ],
            }),
            req('Show', 'GET', 'api/admin/marketing/faqs/1', {
              headers: adminGetHdr(),
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  faq: { id: 1, question_en: 'What is DeepMark?' },
                }),
              ],
            }),
            req('Update', 'PUT', 'api/admin/marketing/faqs/1', {
              headers: adminHdr(),
              body: { question_en: 'Updated?', answer_en: 'Updated answer' },
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  faq: { id: 1, question_en: 'Updated?' },
                }),
              ],
            }),
            req('Delete', 'DELETE', 'api/admin/marketing/faqs/1', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', message: 'Deleted', id: 1 })],
            }),
          ],
        },
        {
          name: 'Pricing',
          item: [
            req('List', 'GET', 'api/admin/marketing/pricing', {
              headers: adminGetHdr(),
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  packages: [{ id: 1, slug: 'pro', name_en: 'Pro' }],
                }),
              ],
            }),
            req('Create', 'POST', 'api/admin/marketing/pricing', {
              headers: adminHdr(),
              body: {
                slug: 'pro',
                name_en: 'Pro',
                name_ar: 'برو',
                price_display: '19.99',
                currency_symbol: '$',
                description_en: 'For teams',
                features_en: ['Unlimited generations'],
                badge_en: 'Popular',
                is_recommended: true,
                cta_label_en: 'Subscribe',
                cta_url: '/subscribe',
                is_active: true,
                sort_order: 1,
              },
              responses: [
                resp(
                  'Created',
                  201,
                  { status: 'OK', package: { id: 1, slug: 'pro' } },
                  'Created'
                ),
              ],
            }),
            req('Show', 'GET', 'api/admin/marketing/pricing/1', {
              headers: adminGetHdr(),
              responses: [
                resp('Success', 200, { status: 'OK', package: { id: 1, slug: 'pro' } }),
              ],
            }),
            req('Update', 'PUT', 'api/admin/marketing/pricing/1', {
              headers: adminHdr(),
              body: { name_en: 'Pro Plus', is_recommended: true },
              responses: [
                resp('Success', 200, { status: 'OK', package: { id: 1, slug: 'pro' } }),
              ],
            }),
            req('Delete', 'DELETE', 'api/admin/marketing/pricing/1', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', message: 'Deleted', id: 1 })],
            }),
          ],
        },
        {
          name: 'Contact Submissions',
          item: [
            req('List', 'GET', 'api/admin/marketing/contact-submissions', {
              headers: adminGetHdr(),
              query: [['per_page', '15']],
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  submissions: [
                    {
                      id: 1,
                      name: 'Jane Doe',
                      email: 'jane@example.com',
                      is_read: false,
                    },
                  ],
                  pagination: { current_page: 1, per_page: 15, total: 1, last_page: 1 },
                }),
              ],
            }),
            req('Show', 'GET', 'api/admin/marketing/contact-submissions/1', {
              headers: adminGetHdr(),
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  submission: {
                    id: 1,
                    name: 'Jane Doe',
                    email: 'jane@example.com',
                    is_read: false,
                  },
                }),
              ],
            }),
            req('Mark Read', 'PUT', 'api/admin/marketing/contact-submissions/1', {
              headers: adminHdr(),
              body: { is_read: true },
              responses: [
                resp('Success', 200, {
                  status: 'OK',
                  submission: { id: 1, is_read: true },
                }),
              ],
            }),
            req('Delete', 'DELETE', 'api/admin/marketing/contact-submissions/1', {
              headers: adminGetHdr(),
              responses: [resp('Success', 200, { status: 'OK', message: 'Deleted', id: 1 })],
            }),
          ],
        },
      ],
    },
  ],
  variable: [
    { key: 'base_url', value: 'http://localhost' },
    { key: 'token', value: '' },
    { key: 'admin_token', value: '' },
  ],
};

function countRequests(items) {
  let n = 0;
  for (const it of items) {
    if (it.item) n += countRequests(it.item);
    else if (it.request) n += 1;
  }
  return n;
}

const outPath = join(__dirname, 'DeepMark.postman_collection.json');
writeFileSync(outPath, JSON.stringify(collection, null, '\t') + '\n', 'utf8');

const env = {
  id: '6f2f1f7b-6c0c-4ec9-8a2f-4c2a2ab3b8c1',
  name: 'DeepMark Local',
  values: [
    { key: 'base_url', value: 'http://localhost', enabled: true },
    { key: 'token', value: '', enabled: true },
    { key: 'admin_token', value: '', enabled: true },
  ],
  _postman_variable_scope: 'environment',
  _postman_exported_at: new Date().toISOString(),
  _postman_exported_using: 'DeepMark generate-collection.mjs',
};

writeFileSync(
  join(__dirname, 'DeepMark.postman_environment.json'),
  JSON.stringify(env, null, '\t') + '\n',
  'utf8'
);

console.log(`Wrote ${outPath}`);
console.log(`Requests: ${countRequests(collection.item)}`);
