# FOXFIRE PEPTIDES — Design & UX Specification (DESIGN.md)

**Prepared by:** Kit D., Full-Stack Developer (Davao, Philippines)\
**Companion document to:** PRD.md\
**Official Domain:** FoxfirePeptides.com\
**Status:** Approved Design System v1.2

---

## 1. Design Philosophy

The site must let a visitor immediately understand: what Foxfire Peptides is, what compounds are in stock, how to select strengths and quantities, how testing/COA verification works, and how to complete checkout quickly. The storefront is deliberately focused and product-first; it must not resemble a huge peptide warehouse, an overly clinical laboratory site, or a generic WooCommerce installation.

Five pillars guide every interface decision:

1. **Simplicity & Speed** — No unnecessary clicks, popups, or scientific jargon that distracts from shopping. Clean, fast catalog browsing inspired by modern benchmarks (e.g. *Crush*).
2. **Readability** — High contrast, generous type sizes (16px minimum body), clear spacing, designed with a ~50-year-old primary customer in mind.
3. **Transparent Quality** — Batch lot numbers and Certificate of Analysis (COA) reports are one click away, presented cleanly without intimidating chemistry essays.
4. **Frictionless Conversion** — Product → Strength → Administrator-configured vial quantity → Cart Drawer → Checkout.
5. **Human Accountability** — Products remain primary, while concise founder and community content shows that a real, publicly associated owner stands behind Foxfire.

---

## 2. Brand Personality & Tone ✅

**Tone:** Professional · Trustworthy · Clean · Approachable · Transparent · Quality-Focused.

- **Official Brand Name:** **FOXFIRE PEPTIDES** (Strictly Foxfire Peptides; eliminate any references to "Foxfire Research", "Foxfire Life Sciences", or "Bandit Lab").
- **Brand Pillars:** **Quality • Transparency • Community**.
- **Community Identity:** *"The Road Is Better Together"* (used warmly in footer/community copy, customer messaging, and future membership experiences).
- **Testing / COA Lead-in:** *"Know What’s Behind the Vial"* is the preferred concise introduction to batch/COA access; it must not be expanded into unsupported testing claims.
- **Founder Presence:** A future *"Meet Jay / Founder of Foxfire"* section uses a real, client-approved photograph and short personal story. It supports the homepage rather than dominating it.
- **Fox Symbol:** The fox/logo should mature into a recognizable cross-channel symbol for the website, packaging, apparel, and social media. Use only final client-approved production assets.
- **Communication Voice:** Personal, friendly, and helpful order/shipping communications rather than robotic WooCommerce defaults.

---

## 3. Color System & Visual Hierarchy ✅

| Role | Color Token | Hex / Value | Example Usage |
|---|---|---|---|
| **Base Background** | `--color-bg` | `#ffffff` (Crisp White) | Dominant page background, product cards, checkout fields |
| **Subtle Neutral** | `--color-bg-subtle` | `#f7f7f7` (Soft Gray) | Table headers, highlight boxes, subtle card borders |
| **Primary Action (CTA)** | `--color-accent-primary` | `#c2410c` (accessible Fox Orange) | Primary CTA buttons, Add to Cart, Checkout, active states |
| **Functional Trust Accent** | `--color-accent-secondary` | `#157a42` (accessible Forest Green) | Reserved exclusively for *In Stock*, *COA Available*, *Verified* badges |
| **Text & Contrast** | `--color-text` | `#1f1f1f` (Charcoal Black) | Primary headings, readable body text (WCAG AAA compliant) |
| **Structural Neutral** | `--color-structure` | `#2b2b2b` (Deep Charcoal) | Footer background, strong divider sections |

### Visual Balance Rules:
- **Predominantly White:** The site is clean, light, and airy. It is never dark, neon-saturated, or clinical.
- **Orange for Action:** Orange is the primary conversion color. All primary buttons and key links use Fox Orange.
- **Neon Green for Trust & Status:** Neon Green is used **selectively and purposefully** — badge pills (*In Stock*, *COA Available*, *Verified*), checkmarks, and order success indicators. It never fills large background areas.

---

## 4. Typography & Readability ✅

- **Headings:** `DM Sans` (Clean, geometric, confident sans-serif).
- **Body Copy:** `Source Sans 3` (Optimized for on-screen legibility and high scanning comfort).
- **Minimum Body Size:** `16px` on desktop and mobile — non-negotiable for the ~50-year-old target demographic.
- **Line Height:** `1.6` for body text to maintain clear vertical separation between lines.

---

## 5. Product Page (PDP) & Quantity Selector UI ✅

### A. Strength Selection (1 or 2 Strengths)
- Single-strength products display the standard strength label (e.g. `10mg`).
- Two-strength products (e.g., GLP-1s like Retatrutide, Tirzepatide, Semaglutide) display prominent toggle pills:
  ```
  Strength: [ 15mg ]  [ 30mg ]
  ```

### B. Administrator-Configured Vial Purchasing Selector
- Instead of a plain number stepper, the quantity options are presented as interactive purchase pills:
  ```
  Quantity: [ 1 Vial ]  [ 3 Vials (Save 5%) ]  [ 5 Vials (Save 10%) ]
  ```
- **Live Price Updates:** Selecting a multi-vial tier updates the unit price and total price dynamically.
- **Single-Vial Stock Deduction:** Each configured vial quantity adds that exact number of units to the cart. Normal order stock reduction deducts individual vials from the applicable product or variation stock pool, not bundle inventory.
- **Admin Control:** Products → Edit → Product data → Quantity Options. Managers can add/remove quantity rows and set a percentage discount or total price for each row; up to 12 unique positive whole quantities. Existing products retain their previous 1/3/5 settings until edited. Variations can override the pricing for their own strength without changing parent quantity choices.
- **Pricing semantics:** The highest qualifying quantity determines the cart unit price, including quantities between presets. Percentage discounts use the active selected-strength price; a fixed total divided by its configured quantity replaces the unit price. Coupons apply afterward. All quantities represent individual vials, not bundle stock. Validate configuration on the server and preserve prior settings on invalid saves. Variable-product pills show “Choose a strength” until a selection supplies tax-aware server prices.

### C. Prominent, Simplified COA Block
- Positioned prominently below the purchase action.
- Clean presentation:
  * Badge: `✓ Batch Verified`
  * Lot tag: `Batch: FF-XXXXXX`
  * Action: `[ View Certificate of Analysis ↗ ]` (direct link/button to the actual lab report PDF or image).
  * Concise reassurance note without technical chemical jargon.

---

## 6. Testing & COA Portal Layout ✅

The Testing/COA page is radically simplified per client requirements:

1. **Reassuring Hero:** Brief, claim-safe explanation of Foxfire Peptides' commitment to making batch and testing documentation available where the corresponding client-supplied report exists. Never imply every batch is tested unless the launch evidence supports that statement.
2. **Searchable Batch Directory:**
   - Fast filter/search bar (e.g. search by compound name or lot number).
   - Clean tabular cards:
     * **Compound Name** (e.g. *Retatrutide (RETA)*)
     * **Lot / Batch #** (e.g. *FF-RT2601*)
     * **Status** (e.g. *Verified*)
     * **Lab Report** (*View COA PDF / Image* button)
3. **No Unverified Claims:** Omit heavy HPLC/MS chemical descriptions, ISO accreditation claims, or blanket purity percentages unless verified on the specific lab document.

---

## 7. Customer Account Hub ✅

The customer account area provides clear, practical value:

- **Dashboard:** Welcome greeting with quick links to recent orders, addresses, My COAs, and Buy Again.
- **Orders & Live Tracking:** Order history with direct tracking links and friendly status notices.
- **My COAs:** Instant lookup of lab reports corresponding specifically to compounds the customer has purchased.
- **Saved Compounds:** Deferred by client decision on September 7, 2026; do not render a placeholder in Phase 1.
- **Buy Again:** One-click reordering for recurring research supplies.

---

## 8. Mobile-First Optimization ✅

- **Navigation:** Clean sticky header with drawer navigation, cart counter, and account link. No product search in header — the catalog is small enough for direct browsing via category navigation.
- **Tap Targets:** Minimum 44px height across all buttons, selectors, and form inputs.
- **Sticky Add to Cart Bar:** Floating bottom bar on mobile PDP displaying product name, price, and instant Add to Cart action.
- **Checkout:** Single-column layout with formatted inputs, autofill support, and clear payment instructions.
- **Checkout recovery:** Plain-language offline/network notices and a visually consistent recent-order recovery card; never encourage a duplicate submission when the first result is uncertain.

---

## 9. Component Conventions

- **Buttons:** Solid Fox Orange with rounded corners (`6px` border-radius). Generous padding (`12px 24px`).
- **Badges:** Compact pills with bold text.
  * In Stock / Verified: Neon Green background (`#e8f6ee`) with dark green text (`#157a42`).
  * Sale / Highlight: Fox Orange background with white text.
- **Forms:** Large inputs (`48px` height), visible focus rings (`3px` orange glow), permanent top labels for accessibility.
- **Cards:** Clean white surface, subtle 1px border (`#e0e0e0`), no heavy drop shadows.

---

## 10. Client-Approved Brand Evolution Direction — Integration Pending

The following direction was confirmed on September 8, 2026. It is a requirements/design record, not authorization to redesign before the client delivers final materials.

1. **Product-first hierarchy:** Important products and the purchase path appear before extended brand storytelling. RETA, TIRZ, and SEMA are expected to lead the focused catalog; final merchandising order remains client-managed WooCommerce data.
2. **Mobile simplicity:** A customer should reach a priority product, choose its strength and quantity, add it to cart, and proceed to checkout without unnecessary navigation or promotional clutter.
3. **Founder section:** Add a restrained secondary Meet Jay section only after receiving the approved portrait and story. Do not fabricate biography or use generated founder imagery.
4. **COA introduction:** Prefer a short *Know What’s Behind the Vial* lead-in over large blocks of technical homepage copy; detailed evidence belongs in the batch/COA experience.
5. **Asset-system review:** When final logo/color assets arrive, test responsive variants, favicon/social/avatar use, contrast, embroidery/print legibility, and small-size recognition before replacing interim assets.
6. **Change control:** Re-run responsive, accessibility, contrast, performance, and claims review after brand integration. Until then, preserve the validated current layout and purchase flow.

## 11. September 13 Homepage Review and Site-Wide Brand Colors — Approved Local Rollout

The approved brand colors apply across the entire website: the homepage, all other page interiors, and the shared header/footer. They supersede the earlier interim colors in Sections 3 and 9. The current local palette rollout covers the homepage, Shop/catalog, single product pages and shared header/footer; remaining page interiors must adopt the same brand colors as their page-by-page updates are implemented. The earlier palette is not an approved alternative for those pages.

- Brand orange: `#FF5800`; brand green: `#AFF769`; near black: `#15171A`; white: `#FFFFFF`.
- Orange is for primary CTAs, highlights and icons. Preserve the original white button text per the user's September 13 clarification; the client did not request a text-color change. White on `#FF5800` has 3.16:1 contrast, below AA for normal-sized text; this remains an accessibility issue to resolve before launch, not a claim of compliance.
- Green is used sparingly for testing/COA accents. Use near-black text on lime surfaces and dark backing for lime checkmarks; never use lime body text on white.
- Homepage order: Hero → Trust Bar → Quality Approach → 4 Featured Products → Testing/COA → FAQ → Final CTA → Footer.
- Omit homepage research-category cards; retain Shop categories and rename Recovery & Healing to Peptide Research without changing its URL or product assignments.
- Header: Logo → Testing & COAs → About → Contact → Account icon → Cart icon → SHOP RESEARCH COMPOUNDS. On mobile the catalog CTA lives in the navigation drawer.
- Hero heading: Research Compounds. Transparent Testing. Real Accountability.
- Hero description: Third-party testing, clear batch documentation, and straightforward access to the information behind every Foxfire product.
- Hero checks: Batch & COA Access, Simple Ordering, Research Use Only. Keep both catalog/testing buttons and the laboratory-research-only disclaimer.
- Requested hero wording is client-supplied copy, not validation of testing evidence. Launch claims and documents still require verification. Payments remain disabled.
- Quality Approach: “More Than a Storefront.” with the client-supplied introduction and three steps: Testing Information, Batch Identification, Straightforward Ordering. Retain the numbered boxes and near-black section.
- Trust Bar: retain four items with the client-supplied wording, including the explicit not-for-human-consumption statement.
- Featured Research Compounds: add the client-supplied introduction; show only four available products from Foxfire Ops → Homepage Products priorities (extra priorities remain backups). Homepage card buttons say VIEW PRODUCT and navigate to existing product pages without adding anything to the cart. The subsequent Shop review also applies VIEW PRODUCT links to catalog cards.
- Testing callout: “Know What's Behind Every Vial.” with the client-supplied third-party/batch-specific documentation description and VIEW TESTING & COAs button linked to the existing directory.
- Homepage FAQ: seven questions; exclude the account-required question (retain it on the dedicated FAQ page). Replace the multi-vial question/answer with the client-supplied wording; preserve the other questions and admin edit/hide behavior.
- Final homepage CTA: “Ready to Explore Foxfire?” with the client-supplied description and one BROWSE RESEARCH COMPOUNDS button linked to the full catalog. Other pages' CTA sections are unchanged.
- Shared footer: brand plus Shop, Information, and Account link groups. Order History uses WooCommerce's orders endpoint. Until separate policy pages are supplied, Research Use Policy links to the existing Terms research-use section, and Shipping & Returns links to the Shipping Policy with its returns-policy cross-link. Existing policy bodies are not rewritten or represented as legally approved. Show the laboratory-research-only notice before the copyright.

## 12. September 13 Shop/Catalog Review — Approved Local Rollout

- Heading: Research Compounds. Use the client-supplied description about available batch, stock and testing documentation.
- Category order: All Compounds → Blends → GLP-1 Research → Peptide Research → Support Compounds. Preserve existing term IDs, URLs and product assignments when updating labels.
- All Shop/category cards, including live search results, use VIEW PRODUCT links to the existing individual product page. Simple products must not add directly to the cart from the catalog. Actual product-page Add to Cart controls remain unchanged.
- Show the current focused catalog on one page, without pagination. Keep the underlying pagination support available for a future approved positive page limit.
- Apply the approved orange, green, near-black and white palette throughout the catalog, including badges, filters, buttons, search/focus states and empty states. Preserve meaningful error colors and readable neutral text; retain white primary-button text as previously requested.
- Keep the current logo area flexible. Do not invent or replace the primary fox logo, wordmark or favicon; await the exact client-supplied files for the website and other branding.

## 13. September 13 Product Quantity Review — Approved Local Rollout

- Quantity choices are product data, not fixed theme buttons. Edit them at Products → Edit product → Product data → Quantity Options; Add quantity option and Remove change the available presets. Update saves the configuration. Disabling presets restores the standard quantity input.
- Each row supports a percentage discount or a complete multi-vial total price. The highest qualifying rule applies to cart quantities; variation pricing overrides are optional in the normal Variations editor. Parent quantities remain shared by all strengths. Existing 1/3/5 products need no bulk migration.
- Product pills, selected total and mobile sticky price use the same server-calculated pricing. Variable products wait for a strength choice, then refresh all preset prices. Stock-ineligible presets are disabled; if none is available, disable Add to Cart and show a concise neutral stock notice. Stock remains counted in individual vials.
- Keep a saved-settings preview with all configured quantities and clearly identified strengths. Reject malformed/duplicate/non-integer quantities and invalid prices atomically; require a nonce and product edit permissions. Do not require ACF Pro to edit quantity rows.

## 14. September 13 Product Images Review — Approved Local Rollout

- Keep current temporary vial images and the existing Shop layout. Do not redesign, regenerate or refine packaging imagery before final client-approved assets arrive.
- Products → Edit product → Product image → choose/upload image → Set product image → Update is the single main-image management path. Shop cards (including live search), homepage Featured Products cards and the individual product gallery read that product's Media Library attachment, not hard-coded copies.
- Product gallery manages additional photos separately. Optional images in Variations intentionally override the main photo for the selected strength; leave these empty to inherit future main-image changes. Uploading a new file alone does not replace the assigned product image.
- Product-linked thumbnails in cart/checkout also use WooCommerce image data. Standalone homepage hero/brand artwork is a separate asset, not an individual product photo, and does not change when a product image changes.
- Normal product-save cache invalidation remains in place. If a cached staging/public page still shows the old photo after Update, purge the site cache once and reload; no theme edit or re-import is required. Preserve old Media Library files until they are no longer used elsewhere.

## 15. Single Product Page Brand Palette — Approved Local Rollout

- Use approved orange for product prices, purchase buttons, handling/COA icons and selection/focus accents. Preserve white primary-button text, subject to the known pre-launch contrast issue recorded in Section 11.
- Product headings, quantity labels and structural text use near black. Keep readable muted text and neutral surfaces; preserve semantic out-of-stock/error colors.
- Testing/COA links use compact lime surfaces with near-black text; hover reverses to near black with lime text. In-stock dots use lime with dark backing. No lime body text on white.
- Scope tokens to single product pages, including related product cards and the mobile purchase bar. Do not redesign the page, modify quantities/pricing, replace product images or enable payments as part of this palette correction.
