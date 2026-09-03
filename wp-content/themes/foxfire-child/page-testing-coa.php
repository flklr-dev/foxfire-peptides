<?php
/**
 * Template Name: Testing / COA Portal
 *
 * Dedicated trust and quality verification page.
 * Provides a clean, searchable batch-to-COA directory and essential quality statements
 * without unsupported claims.
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
				<?php esc_html_e( 'Quality & Batch Verification', 'foxfire-child' ); ?>
			</p>

			<h1 class="ff-testing-hero__title">
				<?php esc_html_e( 'Testing & COA Information', 'foxfire-child' ); ?>
			</h1>

			<p class="ff-testing-hero__lead">
				<?php esc_html_e( 'Access available testing information and Certificates of Analysis for Foxfire products and batches.', 'foxfire-child' ); ?>
			</p>

			<!-- Quality Approach Pillars -->
			<div class="ff-testing-benchmarks">
				<div class="ff-testing-benchmark">
					<div class="ff-testing-benchmark__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<rect x="3" y="4" width="18" height="16" rx="2"></rect>
							<line x1="7" y1="8" x2="17" y2="8"></line>
							<line x1="7" y1="12" x2="13" y2="12"></line>
						</svg>
					</div>
					<span class="ff-testing-benchmark__title"><?php esc_html_e( 'Batch Information', 'foxfire-child' ); ?></span>
					<span class="ff-testing-benchmark__sub"><?php esc_html_e( 'Product and lot/batch details where available.', 'foxfire-child' ); ?></span>
				</div>

				<div class="ff-testing-benchmark">
					<div class="ff-testing-benchmark__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
							<path d="m9 12 2 2 4-4"></path>
						</svg>
					</div>
					<span class="ff-testing-benchmark__title"><?php esc_html_e( 'Testing Information', 'foxfire-child' ); ?></span>
					<span class="ff-testing-benchmark__sub"><?php esc_html_e( 'View the available testing summary for the selected batch.', 'foxfire-child' ); ?></span>
				</div>

				<div class="ff-testing-benchmark">
					<div class="ff-testing-benchmark__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
							<polyline points="14 2 14 8 20 8"></polyline>
							<line x1="16" y1="13" x2="8" y2="13"></line>
							<line x1="16" y1="17" x2="8" y2="17"></line>
						</svg>
					</div>
					<span class="ff-testing-benchmark__title"><?php esc_html_e( 'COA Access', 'foxfire-child' ); ?></span>
					<span class="ff-testing-benchmark__sub"><?php esc_html_e( 'View the available Certificate of Analysis report.', 'foxfire-child' ); ?></span>
				</div>
			</div>
		</div>
	</header>

	<!-- 2. Live Batch & COA Directory Table -->
	<section class="ff-coa-directory" aria-labelledby="ff-directory-title">
		<div class="ff-coa-directory__header">
			<div>
				<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Batch Directory', 'foxfire-child' ); ?></span>
				<h2 id="ff-directory-title" class="ff-section-header__title">
					<?php esc_html_e( 'Batch Reports & Certificates of Analysis', 'foxfire-child' ); ?>
				</h2>
			</div>

			<!-- Live Directory Search Input -->
			<div class="ff-coa-directory__search">
				<label for="ff-coa-search-input" class="screen-reader-text"><?php esc_html_e( 'Search by compound, lot number, or SKU', 'foxfire-child' ); ?></label>
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
					placeholder="<?php esc_attr_e( 'Search product, SKU, or lot #...', 'foxfire-child' ); ?>"
					data-ff-coa-filter
				/>
			</div>
		</div>

		<?php if ( ! empty( $coa_items ) ) : ?>
			<div class="ff-coa-table-wrap">
				<table class="ff-coa-table">
					<thead>
						<tr>
							<th scope="col" class="ff-coa-col--product"><?php esc_html_e( 'Product', 'foxfire-child' ); ?></th>
							<th scope="col" class="ff-coa-col--lot"><?php esc_html_e( 'Batch / Lot #', 'foxfire-child' ); ?></th>
							<th scope="col" class="ff-coa-col--summary"><?php esc_html_e( 'Testing Summary', 'foxfire-child' ); ?></th>
							<th scope="col" class="ff-coa-col--status"><?php esc_html_e( 'Status', 'foxfire-child' ); ?></th>
							<th scope="col" class="ff-coa-col--action"><?php esc_html_e( 'COA', 'foxfire-child' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $coa_items as $item ) : ?>
							<tr data-ff-coa-row data-compound="<?php echo esc_attr( strtolower( $item['name'] . ' ' . $item['batch_lot'] . ' ' . $item['sku'] . ' ' . $item['testing_summary'] ) ); ?>">
								<td data-label="<?php esc_attr_e( 'Product', 'foxfire-child' ); ?>" class="ff-coa-col--product">
									<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="ff-coa-table__compound-link">
										<?php echo esc_html( $item['name'] ); ?>
									</a>
									<?php if ( ! empty( $item['sku'] ) ) : ?>
										<div class="ff-coa-table__sku-sub">
											<span class="ff-coa-table__sku-label"><?php esc_html_e( 'SKU:', 'foxfire-child' ); ?></span>
											<span class="ff-coa-table__sku-val"><?php echo esc_html( $item['sku'] ); ?></span>
										</div>
									<?php endif; ?>
								</td>
								<td data-label="<?php esc_attr_e( 'Batch / Lot #', 'foxfire-child' ); ?>" class="ff-coa-col--lot">
									<span class="ff-coa-table__lot"><?php echo esc_html( $item['batch_lot'] ); ?></span>
								</td>
								<td data-label="<?php esc_attr_e( 'Testing Summary', 'foxfire-child' ); ?>" class="ff-coa-col--summary">
									<span class="ff-coa-table__summary"><?php echo esc_html( $item['testing_summary'] ); ?></span>
								</td>
								<td data-label="<?php esc_attr_e( 'Status', 'foxfire-child' ); ?>" class="ff-coa-col--status">
									<span class="ff-badge ff-badge--tested">
										<span class="ff-badge__icon" aria-hidden="true">✓</span>
										<?php echo esc_html( $item['status'] ); ?>
									</span>
								</td>
								<td data-label="<?php esc_attr_e( 'COA', 'foxfire-child' ); ?>" class="ff-coa-col--action">
									<?php if ( ! empty( $item['coa_url'] ) && '#' !== $item['coa_url'] ) : ?>
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
										<span class="ff-coa-table__pending">
											<?php esc_html_e( 'On File', 'foxfire-child' ); ?>
										</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p id="ff-coa-empty-msg" class="ff-coa-empty-msg" style="display: none;">
					<?php esc_html_e( 'No matching compound or batch lot number found. Please check your search term or browse our shop.', 'foxfire-child' ); ?>
				</p>
			</div>
		<?php else : ?>
			<div class="ff-coa-directory__empty">
				<p><?php esc_html_e( 'Batch-specific Certificates of Analysis are currently being updated.', 'foxfire-child' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<!-- 3. Research Compliance Notice -->
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
					<?php esc_html_e( 'All compounds listed on this website are distributed strictly for in-vitro laboratory research and analytical development. Foxfire Peptides does not provide medical guidance, dosing advice, or administration recommendations.', 'foxfire-child' ); ?>
				</p>
			</div>
		</div>
	</section>
</div>

<?php
get_footer();
