<?php
/**
 * Template Name: Testing / COA Portal
 *
 * Dedicated trust and quality verification page per DESIGN.md §12 and PRD §5.2.
 * Explains analytical testing methodologies (HPLC + MS), provides a searchable
 * live batch-to-COA directory, and details certificate interpretation.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$coa_items = function_exists( 'foxfire_get_coa_catalog_items' ) ? foxfire_get_coa_catalog_items() : array();
$shop_url  = function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : home_url( '/shop/' );
?>

<div class="ff-testing-page">
	<!-- 1. Hero Header -->
	<header class="ff-testing-hero">
		<div class="ff-testing-hero__inner">
			<p class="ff-testing-hero__eyebrow">
				<?php esc_html_e( 'Analytical Purity & Verification Standards', 'foxfire-child' ); ?>
			</p>

			<h1 class="ff-testing-hero__title">
				<?php esc_html_e( 'Independent Laboratory Testing & Certificates of Analysis', 'foxfire-child' ); ?>
			</h1>

			<p class="ff-testing-hero__lead">
				<?php esc_html_e( 'Every research peptide synthesized for Foxfire Peptides is submitted for rigorous third-party chromatographic purity testing and mass spectrometric sequence confirmation. Batch-matched Certificates of Analysis (COAs) are publicly cataloged for complete laboratory verification.', 'foxfire-child' ); ?>
			</p>

			<!-- Quality Benchmarks Strip -->
			<div class="ff-testing-benchmarks">
				<div class="ff-testing-benchmark">
					<span class="ff-testing-benchmark__value">≥ 99.0%</span>
					<span class="ff-testing-benchmark__title"><?php esc_html_e( 'Target Purity Level', 'foxfire-child' ); ?></span>
					<span class="ff-testing-benchmark__sub"><?php esc_html_e( 'Quantified by HPLC trace', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-testing-benchmark">
					<span class="ff-testing-benchmark__value">HPLC + MS</span>
					<span class="ff-testing-benchmark__title"><?php esc_html_e( 'Dual Analytical Testing', 'foxfire-child' ); ?></span>
					<span class="ff-testing-benchmark__sub"><?php esc_html_e( 'Purity & mass confirmation', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-testing-benchmark">
					<span class="ff-testing-benchmark__value">100% Traceable</span>
					<span class="ff-testing-benchmark__title"><?php esc_html_e( 'Batch-Matched COAs', 'foxfire-child' ); ?></span>
					<span class="ff-testing-benchmark__sub"><?php esc_html_e( 'Unique vial lot verification', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-testing-benchmark">
					<span class="ff-testing-benchmark__value">ISO 17025</span>
					<span class="ff-testing-benchmark__title"><?php esc_html_e( 'Accredited Testing Labs', 'foxfire-child' ); ?></span>
					<span class="ff-testing-benchmark__sub"><?php esc_html_e( 'Independent analytical testing', 'foxfire-child' ); ?></span>
				</div>
			</div>
		</div>
	</header>

	<!-- 2. Two-Tier Analytical Verification -->
	<section class="ff-testing-methods" aria-labelledby="ff-methods-title">
		<div class="ff-section-header">
			<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Analytical Methodology', 'foxfire-child' ); ?></span>
			<h2 id="ff-methods-title" class="ff-section-header__title">
				<?php esc_html_e( 'Our Two-Tier Analytical Testing Protocol', 'foxfire-child' ); ?>
			</h2>
		</div>

		<div class="ff-methods-grid">
			<div class="ff-method-card">
				<div class="ff-method-card__top">
					<span class="ff-method-card__tier">
						<?php esc_html_e( 'Tier 1 · Purity Profiling', 'foxfire-child' ); ?>
					</span>
					<div class="ff-method-card__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
						</svg>
					</div>
				</div>

				<h3 class="ff-method-card__title">
					<?php esc_html_e( 'High-Performance Liquid Chromatography (HPLC)', 'foxfire-child' ); ?>
				</h3>

				<p class="ff-method-card__text">
					<?php esc_html_e( 'HPLC separates chemical components under high pressure to accurately quantify compound purity. Each sample is resolved against analytical reference standards to detect truncated sequences, deletion fragments, and residual synthesis impurities. A sharp, singular chromatographic peak confirms maximum purity.', 'foxfire-child' ); ?>
				</p>

				<dl class="ff-method-specs">
					<div class="ff-method-spec-row">
						<dt class="ff-method-spec-row__label"><?php esc_html_e( 'Resolution Standard', 'foxfire-child' ); ?></dt>
						<dd class="ff-method-spec-row__value"><?php esc_html_e( 'Quantifies target peak area vs. total integrated peak area', 'foxfire-child' ); ?></dd>
					</div>
					<div class="ff-method-spec-row">
						<dt class="ff-method-spec-row__label"><?php esc_html_e( 'Acceptance Criteria', 'foxfire-child' ); ?></dt>
						<dd class="ff-method-spec-row__value"><?php esc_html_e( '≥ 99.0% target peptide purity by area normalization', 'foxfire-child' ); ?></dd>
					</div>
				</dl>
			</div>

			<div class="ff-method-card">
				<div class="ff-method-card__top">
					<span class="ff-method-card__tier">
						<?php esc_html_e( 'Tier 2 · Sequence Confirmation', 'foxfire-child' ); ?>
					</span>
					<div class="ff-method-card__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M3 3v18h18"/><path d="M7 16v-4"/><path d="M11 16V7"/><path d="M15 16v-8"/><path d="M19 16v-2"/>
						</svg>
					</div>
				</div>

				<h3 class="ff-method-card__title">
					<?php esc_html_e( 'Liquid Chromatography-Mass Spectrometry (LC-MS)', 'foxfire-child' ); ?>
				</h3>

				<p class="ff-method-card__text">
					<?php esc_html_e( 'Mass Spectrometry ionizes peptide molecules to measure their mass-to-charge ratio (m/z). This validates exact molecular mass against theoretical sequence molecular weight [M+H]+, definitively verifying chemical identity and confirming the synthesized amino acid sequence.', 'foxfire-child' ); ?>
				</p>

				<dl class="ff-method-specs">
					<div class="ff-method-spec-row">
						<dt class="ff-method-spec-row__label"><?php esc_html_e( 'Mass Precision', 'foxfire-child' ); ?></dt>
						<dd class="ff-method-spec-row__value"><?php esc_html_e( 'Molecular weight confirmation within ±0.5 Da of theoretical mass', 'foxfire-child' ); ?></dd>
					</div>
					<div class="ff-method-spec-row">
						<dt class="ff-method-spec-row__label"><?php esc_html_e( 'Verification Target', 'foxfire-child' ); ?></dt>
						<dd class="ff-method-spec-row__value"><?php esc_html_e( 'Definitive chemical identification and sequence integrity', 'foxfire-child' ); ?></dd>
					</div>
				</dl>
			</div>
		</div>
	</section>

	<!-- 3. Live Batch & COA Directory Table -->
	<section class="ff-coa-directory" aria-labelledby="ff-directory-title">
		<div class="ff-coa-directory__header">
			<div>
				<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Batch Verification Library', 'foxfire-child' ); ?></span>
				<h2 id="ff-directory-title" class="ff-section-header__title">
					<?php esc_html_e( 'Current Catalog Batch & COA Directory', 'foxfire-child' ); ?>
				</h2>
			</div>

			<!-- Live Directory Search Input -->
			<div class="ff-coa-directory__search">
				<label for="ff-coa-search-input" class="screen-reader-text"><?php esc_html_e( 'Filter batch or compound', 'foxfire-child' ); ?></label>
				<span class="ff-coa-directory__search-icon" aria-hidden="true">
					<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="8"/>
						<line x1="21" y1="21" x2="16.65" y2="16.65"/>
					</svg>
				</span>
				<input
					type="text"
					id="ff-coa-search-input"
					class="ff-coa-directory__search-input"
					placeholder="<?php esc_attr_e( 'Search by compound, SKU, or lot #...', 'foxfire-child' ); ?>"
					data-ff-coa-filter
				/>
			</div>
		</div>

		<?php if ( ! empty( $coa_items ) ) : ?>
			<div class="ff-coa-table-wrap">
				<table class="ff-coa-table">
					<thead>
						<tr>
							<th scope="col" style="width: 22%;"><?php esc_html_e( 'Compound Name', 'foxfire-child' ); ?></th>
							<th scope="col" style="width: 14%;"><?php esc_html_e( 'Catalog SKU', 'foxfire-child' ); ?></th>
							<th scope="col" style="width: 22%;"><?php esc_html_e( 'Batch / Lot #', 'foxfire-child' ); ?></th>
							<th scope="col" style="width: 14%;"><?php esc_html_e( 'Analytical Method', 'foxfire-child' ); ?></th>
							<th scope="col" style="width: 13%;"><?php esc_html_e( 'Purity Level', 'foxfire-child' ); ?></th>
							<th scope="col" style="width: 15%; text-align: right;" class="ff-coa-table__action-col"><?php esc_html_e( 'Certificate', 'foxfire-child' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $coa_items as $item ) : ?>
							<tr data-ff-coa-row data-compound="<?php echo esc_attr( strtolower( $item['name'] . ' ' . $item['batch_lot'] . ' ' . $item['sku'] ) ); ?>">
								<td data-label="<?php esc_attr_e( 'Compound', 'foxfire-child' ); ?>">
									<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="ff-coa-table__compound-link">
										<?php echo esc_html( $item['name'] ); ?>
									</a>
								</td>
								<td data-label="<?php esc_attr_e( 'SKU', 'foxfire-child' ); ?>">
									<span class="ff-coa-table__sku"><?php echo esc_html( $item['sku'] ); ?></span>
								</td>
								<td data-label="<?php esc_attr_e( 'Batch / Lot', 'foxfire-child' ); ?>">
									<span class="ff-coa-table__lot"><?php echo esc_html( $item['batch_lot'] ); ?></span>
								</td>
								<td data-label="<?php esc_attr_e( 'Method', 'foxfire-child' ); ?>" class="ff-coa-table__method">
									<?php echo esc_html( $item['method'] ); ?>
								</td>
								<td data-label="<?php esc_attr_e( 'Purity', 'foxfire-child' ); ?>">
									<span class="ff-coa-badge ff-coa-badge--verified">
										<?php echo esc_html( $item['purity'] ); ?>
									</span>
								</td>
								<td data-label="<?php esc_attr_e( 'Certificate', 'foxfire-child' ); ?>" class="ff-coa-table__action-col">
									<?php if ( '#' !== $item['coa_url'] && '' !== $item['coa_url'] ) : ?>
										<a
											href="<?php echo esc_url( $item['coa_url'] ); ?>"
											class="ff-coa-btn"
											target="_blank"
											rel="noopener noreferrer"
										>
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
												<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
												<polyline points="14 2 14 8 20 8"/>
												<line x1="16" y1="13" x2="8" y2="13"/>
												<line x1="16" y1="17" x2="8" y2="17"/>
											</svg>
											<?php esc_html_e( 'View COA', 'foxfire-child' ); ?>
										</a>
									<?php else : ?>
										<span class="ff-coa-table__pending" title="<?php esc_attr_e( 'Lot documentation archived with analytical lab', 'foxfire-child' ); ?>">
											<?php esc_html_e( 'Matched on File', 'foxfire-child' ); ?>
										</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p id="ff-coa-empty-msg" class="ff-coa-empty-msg" style="display: none;">
					<?php esc_html_e( 'No matching compound or batch lot number found. Please check your lot code or contact research support.', 'foxfire-child' ); ?>
				</p>
			</div>
		<?php else : ?>
			<div class="ff-coa-directory__empty">
				<p><?php esc_html_e( 'Batch-specific Certificates of Analysis are currently being updated in the analytical repository.', 'foxfire-child' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<!-- 4. How to Read a Foxfire COA -->
	<section class="ff-coa-guide" aria-labelledby="ff-guide-title">
		<div class="ff-section-header">
			<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Document Breakdown', 'foxfire-child' ); ?></span>
			<h2 id="ff-guide-title" class="ff-section-header__title">
				<?php esc_html_e( 'How to Read a Certificate of Analysis (COA)', 'foxfire-child' ); ?>
			</h2>
		</div>

		<div class="ff-coa-guide-container">
			<div class="ff-coa-guide-list">
				<div class="ff-coa-guide-item">
					<div class="ff-coa-guide-item__num">01</div>
					<div class="ff-coa-guide-item__body">
						<h3 class="ff-coa-guide-item__title"><?php esc_html_e( 'Accredited Laboratory Header', 'foxfire-child' ); ?></h3>
						<p class="ff-coa-guide-item__desc"><?php esc_html_e( 'Identifies the independent ISO/IEC 17025 accredited testing facility, report issue date, and official laboratory reference ID.', 'foxfire-child' ); ?></p>
					</div>
				</div>

				<div class="ff-coa-guide-item">
					<div class="ff-coa-guide-item__num">02</div>
					<div class="ff-coa-guide-item__body">
						<h3 class="ff-coa-guide-item__title"><?php esc_html_e( 'Compound Name & Batch Lot #', 'foxfire-child' ); ?></h3>
						<p class="ff-coa-guide-item__desc"><?php esc_html_e( 'Confirms the chemical name, molecular weight, and the unique lot number that matches the printed label on your vial.', 'foxfire-child' ); ?></p>
					</div>
				</div>

				<div class="ff-coa-guide-item">
					<div class="ff-coa-guide-item__num">03</div>
					<div class="ff-coa-guide-item__body">
						<h3 class="ff-coa-guide-item__title"><?php esc_html_e( 'HPLC Purity Analysis', 'foxfire-child' ); ?></h3>
						<p class="ff-coa-guide-item__desc"><?php esc_html_e( 'Quantifies the exact chemical purity percentage through High-Performance Liquid Chromatography (target purity ≥ 99.0%).', 'foxfire-child' ); ?></p>
					</div>
				</div>

				<div class="ff-coa-guide-item">
					<div class="ff-coa-guide-item__num">04</div>
					<div class="ff-coa-guide-item__body">
						<h3 class="ff-coa-guide-item__title"><?php esc_html_e( 'Mass Spectrometry Sequence Confirmation', 'foxfire-child' ); ?></h3>
						<p class="ff-coa-guide-item__desc"><?php esc_html_e( 'Measures the molecular mass against theoretical sequence weight [M+H]+, definitively verifying the peptide identity.', 'foxfire-child' ); ?></p>
					</div>
				</div>

				<div class="ff-coa-guide-item">
					<div class="ff-coa-guide-item__num">05</div>
					<div class="ff-coa-guide-item__body">
						<h3 class="ff-coa-guide-item__title"><?php esc_html_e( 'Authorized Chemist Review & Seal', 'foxfire-child' ); ?></h3>
						<p class="ff-coa-guide-item__desc"><?php esc_html_e( 'Official analytical chemist verification signature and laboratory release seal validating lot compliance.', 'foxfire-child' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 5. Handling & Storage Standards -->
	<section class="ff-storage-standards" aria-labelledby="ff-storage-title">
		<div class="ff-section-header">
			<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Laboratory Protocol', 'foxfire-child' ); ?></span>
			<h2 id="ff-storage-title" class="ff-section-header__title">
				<?php esc_html_e( 'Lyophilization & Cold-Chain Storage Standards', 'foxfire-child' ); ?>
			</h2>
		</div>

		<div class="ff-storage-grid">
			<div class="ff-storage-card">
				<div class="ff-storage-card__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/>
					</svg>
				</div>
				<h3 class="ff-storage-card__title"><?php esc_html_e( 'Temperature Controlled Storage', 'foxfire-child' ); ?></h3>
				<span class="ff-storage-card__spec"><?php esc_html_e( 'Storage Standard: -20°C to -80°C', 'foxfire-child' ); ?></span>
				<p class="ff-storage-card__desc"><?php esc_html_e( 'Lyophilized peptide solids should be stored desiccated at -20°C for long-term molecular stability. Protect from direct light exposure and minimize repeated freeze-thaw cycles.', 'foxfire-child' ); ?></p>
			</div>

			<div class="ff-storage-card">
				<div class="ff-storage-card__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
						<path d="m9 12 2 2 4-4"/>
					</svg>
				</div>
				<h3 class="ff-storage-card__title"><?php esc_html_e( 'Sterile Nitrogen Purge', 'foxfire-child' ); ?></h3>
				<span class="ff-storage-card__spec"><?php esc_html_e( 'Atmosphere: Inert Nitrogen Flush', 'foxfire-child' ); ?></span>
				<p class="ff-storage-card__desc"><?php esc_html_e( 'Vials are hermetically crimped under a sterile, inert nitrogen atmosphere to prevent atmospheric oxidation, humidity absorption, and peptide degradation in transit.', 'foxfire-child' ); ?></p>
			</div>

			<div class="ff-storage-card">
				<div class="ff-storage-card__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 8v13H3V8"/>
						<path d="M1 3h22v5H1z"/>
						<path d="M10 12h4"/>
					</svg>
				</div>
				<h3 class="ff-storage-card__title"><?php esc_html_e( '24-Month Retention Archiving', 'foxfire-child' ); ?></h3>
				<span class="ff-storage-card__spec"><?php esc_html_e( 'Audit Window: 24-Month Library', 'foxfire-child' ); ?></span>
				<p class="ff-storage-card__desc"><?php esc_html_e( 'Every discrete synthesis batch maintains control reserve vials in certified climate-controlled archiving for 24 months, enabling retrospective analytical audits at any time.', 'foxfire-child' ); ?></p>
			</div>
		</div>
	</section>

	<!-- 6. Testing & Quality FAQ -->
	<section class="ff-testing-faq" aria-labelledby="ff-faq-title">
		<div class="ff-section-header">
			<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Common Inquiries', 'foxfire-child' ); ?></span>
			<h2 id="ff-faq-title" class="ff-section-header__title">
				<?php esc_html_e( 'Frequently Asked Questions About Our Testing', 'foxfire-child' ); ?>
			</h2>
		</div>

		<div class="ff-faq-list">
			<details class="ff-faq-item">
				<summary class="ff-faq-item__question">
					<span><?php esc_html_e( 'How often are Foxfire Peptides synthesis batches tested?', 'foxfire-child' ); ?></span>
					<span class="ff-faq-item__toggle" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
					</span>
				</summary>
				<div class="ff-faq-item__answer">
					<p><?php esc_html_e( 'Testing is performed on 100% of discrete manufacturing lots prior to inventory release. Each production batch receives an independent Certificate of Analysis corresponding to its unique laser-etched vial lot number.', 'foxfire-child' ); ?></p>
				</div>
			</details>

			<details class="ff-faq-item">
				<summary class="ff-faq-item__question">
					<span><?php esc_html_e( 'Are testing laboratories independent from Foxfire Peptides?', 'foxfire-child' ); ?></span>
					<span class="ff-faq-item__toggle" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
					</span>
				</summary>
				<div class="ff-faq-item__answer">
					<p><?php esc_html_e( 'Yes. All analytical testing is performed exclusively by accredited, third-party independent analytical testing facilities operating under standard chromatographic and mass spectrometric protocols.', 'foxfire-child' ); ?></p>
				</div>
			</details>

			<details class="ff-faq-item">
				<summary class="ff-faq-item__question">
					<span><?php esc_html_e( 'How do I locate the lot number on my vial?', 'foxfire-child' ); ?></span>
					<span class="ff-faq-item__toggle" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
					</span>
				</summary>
				<div class="ff-faq-item__answer">
					<p><?php esc_html_e( 'Every Foxfire Peptides vial features a lot identifier printed on the right side of the main label (formatted as FF-XXXXXX). Enter this code into our directory search above to view the matching report.', 'foxfire-child' ); ?></p>
				</div>
			</details>

			<details class="ff-faq-item">
				<summary class="ff-faq-item__question">
					<span><?php esc_html_e( 'Can research institutions request raw analytical data?', 'foxfire-child' ); ?></span>
					<span class="ff-faq-item__toggle" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
					</span>
				</summary>
				<div class="ff-faq-item__answer">
					<p><?php esc_html_e( 'Yes. Verified laboratory institutions may contact our scientific support team with their order number and lot code to request extended integration data or raw spectra files for analytical audits.', 'foxfire-child' ); ?></p>
				</div>
			</details>
		</div>
	</section>

	<!-- 7. Research Compliance Notice -->
	<section class="ff-compliance-notice" aria-label="<?php esc_attr_e( 'Regulatory Notice', 'foxfire-child' ); ?>">
		<div class="ff-compliance-notice__inner">
			<div class="ff-compliance-notice__icon" aria-hidden="true">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
					<line x1="12" y1="8" x2="12" y2="12"/>
					<line x1="12" y1="16" x2="12.01" y2="16"/>
				</svg>
			</div>
			<div>
				<h3 class="ff-compliance-notice__title"><?php esc_html_e( 'Laboratory Research Use Only', 'foxfire-child' ); ?></h3>
				<p class="ff-compliance-notice__text">
					<?php esc_html_e( 'All compounds listed on this site are manufactured strictly for in-vitro laboratory research, scientific inquiry, and analytical testing applications. These products are not intended for human or animal diagnostic, therapeutic, or consumption use. Foxfire Peptides does not provide medical guidance or administration instructions.', 'foxfire-child' ); ?>
				</p>
			</div>
		</div>
	</section>
</div>

<?php
get_footer();
