# IUC Edu Website

PHP website for IUC Edu, a computer and IT training institute in Chennai. The site runs on Apache/PHP, uses clean public URLs through `mod_rewrite`, and stores enquiries in MySQL when the database is available.

## Recent SEO update

Update date: 25 August 2026
Production origin: `https://www.iucedu.com`

This update establishes one canonical URL format, gives every indexable page unique search and social metadata, adds structured data, expands search-intent content, improves internal linking and image semantics, and publishes explicit crawl/index controls.

### Main outcomes

- Production URLs are consolidated to HTTPS, the `www` host, extensionless paths and no trailing slash.
- Old query-string course and blog URLs redirect permanently to clean paths.
- The homepage, course pages, blog pages and four new intent landing pages have unique titles, descriptions, canonicals and social metadata.
- Reusable JSON-LD provides organization, location, website and webpage entities; individual templates add Course, Blog, BlogPosting, BreadcrumbList, FAQPage and ItemList entities.
- Invalid course, blog and landing routes return HTTP 404, use `noindex, follow`, and omit canonical URLs.
- `robots.txt` and a static production sitemap now define the intended crawl surface.
- Contextual links connect the homepage, landing pages, courses and blog articles.
- Important images have descriptive alternative text; known image dimensions and loading priority are supplied where appropriate.
- The analytics script is deferred, and MySQL failure no longer prevents public pages from rendering.

## Public URL inventory

| Page type | Public URL | Handler |
| --- | --- | --- |
| Homepage | `/` | `index.php` |
| Blog index | `/blog` | `blog.php` |
| Blog article | `/blog/{slug}` | `blog.php?slug={slug}` internally |
| Course detail | `/course/{slug}` | `course.php?slug={slug}` internally |
| Computer training landing page | `/computer-training-in-chennai` | `landing.php?page=computer-training-in-chennai` internally |
| Programming landing page | `/programming-courses-in-chennai` | `landing.php?page=programming-courses-in-chennai` internally |
| Beginner-course landing page | `/it-courses-for-beginners` | `landing.php?page=it-courses-for-beginners` internally |
| Online-course landing page | `/online-it-courses` | `landing.php?page=online-it-courses` internally |
| XML sitemap | `/sitemap.xml` | `sitemap.xml` |
| Crawler rules | `/robots.txt` | `robots.txt` |

The query-string forms in the Handler column are internal Apache rewrites. They should not be used in navigation, advertising, sitemap submissions or canonical tags.

## SEO implementation details

### Shared metadata contract

Page controllers set variables before loading `includes/header.php`:

| Variable | Purpose | Default behavior |
| --- | --- | --- |
| `$pageTitle` | HTML title and default social title | Site-wide Chennai IT training title |
| `$metaDesc` | Meta description and default social description | Site-wide course summary |
| `$canonical` | Path appended to `SITE_URL` | Empty path produces the homepage URL |
| `$canonical = false` | Suppresses canonical and `og:url` | Used for error pages |
| `$robotsMeta` | Per-page robots directive | `index, follow` plus unrestricted preview directives |
| `$ogTitle`, `$ogDesc` | Optional Open Graph overrides | Falls back to title and description |
| `$ogImage`, `$ogImageAlt` | Social image and accessible description | Uses the default education image |
| `$ogType` | Open Graph content type | `website`; articles set `article` |
| `$articlePublishedTime`, `$articleAuthor` | Article metadata | Output only when supplied |
| `$schemaPageType` | Base webpage JSON-LD type | `WebPage` |
| `$structuredData` | One schema object or a list of objects | Appended to the shared JSON-LD graph |

All dynamic metadata and JSON-LD values are escaped or encoded in `includes/header.php`.

### Structured data graph

Every normal page receives these shared entities:

- `EducationalOrganization` and `LocalBusiness` for IUC Edu's C.I.T Nagar office.
- A second `EducationalOrganization` and `LocalBusiness` department for the Thiruvottiyur centre.
- `WebSite` with the organization as publisher.
- `WebPage` or the page-specific subtype, connected to the website and organization.

Templates then add:

- Homepage: `FAQPage`.
- Course detail: `Course`, `BreadcrumbList` and course-specific `FAQPage`.
- Blog index: `Blog` and `BreadcrumbList`.
- Blog article: `BlogPosting` and `BreadcrumbList`.
- Intent landing page: `BreadcrumbList`, `ItemList` and `FAQPage`.

The final output is one `application/ld+json` block containing an `@graph`.

### Canonicalization and redirects

`.htaccess` implements the public URL policy:

- `http://iucedu.com/...`, `https://iucedu.com/...` and `http://www.iucedu.com/...` redirect to `https://www.iucedu.com/...`.
- `/course.php?slug=python` redirects to `/course/python`.
- `/blog.php?slug=top-programming-languages-2026` redirects to `/blog/top-programming-languages-2026`.
- `/index.php` redirects to `/`.
- Non-directory URLs ending in `/` redirect to the equivalent path without the slash.
- Extensionless routes are internally mapped to PHP handlers.
- Production-host redirect conditions exclude localhost/XAMPP development.

The redirect and canonical target must remain consistent with the `SITE_URL` constant in `includes/functions.php`.

### Crawl and index controls

`robots.txt` allows public crawling and disallows internal or non-search pages:

- `/admin/`
- `/track.php`
- `/_probe.php`
- `/download-syllabus.php`
- `/landing.php`
- `/index_iuc29072026.php.bak`

It also advertises `https://www.iucedu.com/sitemap.xml`.

The sitemap lists 25 canonical URLs: the homepage, four intent pages, the blog index, 16 course pages and three blog articles. It is now static XML, so new, removed or renamed public content must be updated manually.

### Content and internal linking

- Homepage copy now directly describes computer and IT training in Chennai.
- `includes/seo-training.php` adds a homepage section linking to the four intent pages and five popular course pages.
- `landing.php` provides substantial, distinct content for four user intents, including recommended courses and page-specific FAQs.
- Course pages link to the relevant programming or computer-training hub, the beginner hub when applicable, and the online-course hub.
- Related courses use a curated slug map instead of category-only matching.
- Blog articles link back to the blog index and, when mapped, to a relevant course.
- Footer links expose all new hubs, the blog index and the sitemap site-wide.

### Error-page behavior

The root 404 handler and invalid course, blog or landing slugs now:

- return an actual 404 status;
- use a useful page title and description;
- output `noindex, follow`;
- omit canonical and Open Graph URL tags;
- provide a working path back to current content.

## File-by-file update record

| File | Status | Update details |
| --- | --- | --- |
| `.htaccess` | Modified | Enforces the canonical production origin, redirects legacy detail URLs and `/index.php`, removes trailing slashes, adds four landing-page rewrites and routes missing files to the project 404 page. |
| `404.php` | Modified | Sends HTTP 404, sets a specific description, applies `noindex, follow`, and suppresses canonical output. |
| `blog.php` | Modified | Adds a real `/blog` index, unique article SEO fields, article Open Graph data, Blog/BlogPosting/Breadcrumb schemas, noindex error handling, semantic article rendering, contextual course links and improved image metadata. |
| `course.php` | Modified | Adds unique title/H1/description mappings for 16 courses, canonical/social data, Course/Breadcrumb/FAQ schemas, intent-hub links, curated related courses, course-specific FAQs and improved hero image metadata. |
| `db.php` | Modified | Wraps MySQL initialization in exception handling so a database outage does not take public SEO pages offline. |
| `includes/about.php` | Modified | Improves image alternative text and adds known width/height attributes. |
| `includes/ai-course.php` | Modified | Replaces generic AI image text with a descriptive course/location alternative. |
| `includes/blog.php` | Modified | Changes the homepage blog CTA from an on-page anchor to the crawlable `/blog` index. |
| `includes/faq.php` | Modified | Builds the contact link from `BASE_URL`, preserving correct behavior in both production and the XAMPP subdirectory. |
| `includes/footer.php` | Modified | Adds site-wide links to all four intent pages, blog and sitemap; improves logo metadata; replaces placeholder policy anchors with non-link text. |
| `includes/functions.php` | Modified | Rewrites homepage FAQs around real search/user questions, adds dedicated SEO titles/descriptions to blog data, and adds a safe formatter for article headings, lists, paragraphs and bold text. |
| `includes/header.php` | Modified | Centralizes escaped metadata, canonical/robots controls, Open Graph and Twitter cards, article fields, and the reusable JSON-LD graph; sets `en-IN`, defers analytics, supplies logo dimensions and marks the admin link `nofollow`. |
| `includes/hero.php` | Modified | Aligns the homepage H1 and introduction with computer/IT training intent, repairs the brochure CTA, treats decorative avatars correctly and optimizes the main image metadata/loading priority. |
| `includes/internship.php` | Modified | Adds descriptive image alternative text and intrinsic dimensions. |
| `includes/seo-training.php` | New | Adds the homepage's intent-focused training cards and contextual popular-course links. |
| `index.php` | Modified | Sets the homepage title, description, social image, canonical and FAQ schema; includes the new SEO training section. |
| `landing.php` | New | Serves four allow-listed, intent-specific landing pages with unique copy, course lists, breadcrumbs, FAQs, canonicals, social metadata and structured data; rejects unknown keys with a noindex 404. |
| `robots.txt` | New | Defines public crawl access, blocks internal endpoints and declares the production sitemap. |
| `sitemap.xml` | Modified | Replaces dynamic PHP output with a valid static XML list of 25 canonical production URLs and content-specific modification dates. |

`assets/js/tracker.js` was not changed as part of this SEO update; only its script loading mode in `includes/header.php` changed to `defer`.

## Admin keyword performance monitor

The **SEO Monitor** tab in `/admin/` now combines two different data sources:

- **Google Search Console Search Analytics**: organic Google queries, clicks, impressions, CTR, average position, previous-period changes, top landing page, brand/non-brand split, device, country and search-appearance data.
- **On-site analytics**: visitor sessions, referrer data and campaign `utm_term` values that can be connected to an enquiry or conversion.

Search Console data is aggregated. It cannot reveal which individual person searched a keyword, and Google can omit anonymized queries. Enquiry-level keyword attribution is possible only when the incoming campaign includes a value such as `utm_term=python+course+chennai`.

### Connect Google Search Console

1. In Google Cloud, enable the **Google Search Console API** and create a service account with a JSON key.
2. Store the JSON key outside the Apache/public project directory, for example `C:/secure/iuc-search-console-service-account.json`.
3. In Google Search Console, open the `iucedu.com` property settings and add the service-account `client_email` as a user with read access.
4. Sign in to `/admin/`, open **SEO Monitor**, enter the exact property and upload the JSON key under **Connect real Search Console data**. This connection is session-only: the private key is not written to the project or database and is forgotten when the admin session ends.
5. This site currently uses the verified URL-prefix property `https://www.iucedu.com/`. A Domain property must instead use its exact `sc-domain:` value.
6. Choose the date range and select **Refresh GSC**. If Google reports a permission error, confirm that the uploaded JSON's `client_email` was added to the same Search Console property.

For a persistent production connection, copy `includes/search-console-config.example.php` to the ignored `includes/search-console-config.php` and point it to a JSON key stored outside the public web root, or configure the environment variables below.

Production can use server environment variables instead of a PHP config file:

| Variable | Purpose | Default |
| --- | --- | --- |
| `GSC_PROPERTY` | Exact Search Console property | `https://www.iucedu.com/` |
| `GSC_CREDENTIALS_FILE` | Absolute path to the service-account JSON | none |
| `GSC_CREDENTIALS_JSON` | JSON supplied directly by a secret manager | none |
| `GSC_CACHE_TTL` | Per-admin-session API cache in seconds | `600` |
| `GSC_ROW_LIMIT` | Search Analytics row limit, maximum 25,000 | `5000` |
| `GSC_BRAND_TERMS` | Comma-separated brand-query phrases | IUC brand variants |

Never place a real service-account key in source control. The connection endpoints require an authenticated admin session plus CSRF validation, accept only a valid service-account JSON/private key under 100 KB, use the read-only Search Console OAuth scope, and never return the private key to the browser.

## Local setup

1. Place the project at `C:\xampp\htdocs\iuc-website`.
2. Start Apache in XAMPP. Start MySQL if enquiry persistence is required; public pages remain available without it.
3. Confirm Apache `mod_rewrite` is enabled and `.htaccess` overrides are allowed.
4. Open `http://localhost/iuc-website/`.
5. Test clean routes such as `http://localhost/iuc-website/course/python` and `http://localhost/iuc-website/programming-courses-in-chennai`.

`BASE_URL` is derived from the document root, so internal links include `/iuc-website` locally and use the domain root in production. SEO canonicals always use the production `SITE_URL`.

## Validation checklist

Before deployment:

1. Run PHP syntax checks on every PHP file.
2. Parse `sitemap.xml` as XML and confirm every listed production URL returns 200.
3. Confirm `robots.txt` returns `text/plain` and points to the correct sitemap.
4. Check that one redirect reaches the final URL without a redirect chain:
   - HTTP to HTTPS + `www`.
   - Non-`www` to `www`.
   - `.php?slug=` to the clean detail route.
   - A trailing-slash URL to the no-slash URL.
5. Inspect rendered source for one homepage, course, blog article and landing page:
   - one title;
   - one meta description;
   - one canonical;
   - correct robots directive;
   - complete Open Graph/Twitter fields;
   - valid JSON-LD matching visible content.
6. Confirm invalid course, blog and landing slugs return 404 with `noindex, follow` and no canonical.
7. Validate schema output with Google's Rich Results Test or Schema.org Validator.
8. Check mobile rendering and Core Web Vitals, especially externally hosted course/blog images.
9. After deployment, submit the sitemap in Google Search Console and monitor indexing, canonical selection, structured-data reports and 404s.

## Maintenance rules

When adding or changing an indexable page:

1. Give it a unique title, H1 and description matching the page's actual intent.
2. Set its canonical path before including `includes/header.php`.
3. Add only structured data that is supported by visible page content.
4. Link it from a relevant crawlable page; do not rely only on the sitemap.
5. Add or update its canonical URL and honest `lastmod` value in `sitemap.xml`.
6. Preserve the no-trailing-slash URL convention.
7. Use a real 404 plus `noindex, follow` for unknown dynamic slugs.
8. Add descriptive `alt` text to informative images and empty `alt` text to decorative images.
9. Update this file-by-file record when the implementation changes.

## Important deployment notes

- The production host is intentionally hard-coded as `https://www.iucedu.com`. Change `.htaccess`, `SITE_URL`, `robots.txt` and `sitemap.xml` together if the canonical domain changes.
- `sitemap.xml` is static. Updating the `$courses` or `$blogPosts` arrays does not update it automatically.
- Apache rewrite rules are required for all clean course, blog and intent-page URLs.
- Search appearance and ranking are not guaranteed by technical SEO alone; Search Console data, content quality, authority and ongoing maintenance still matter.
