<?php
/**
 * Seed script for SpiceCraft Blog / News & Articles CMS
 */

require_once __DIR__ . '/../wp-load.php';

echo "=== SEEDING SPICECRAFT BLOG SYSTEM ===\n";

// 1. Ensure /blog/ page exists and has page-blog.php template
$blog_page_id = function_exists( 'spicecraft_ensure_blog_page' ) ? spicecraft_ensure_blog_page() : 0;
echo "1. Blog Page ensured: ID {$blog_page_id} (/blog/)\n";

// 2. Set default blog settings if not set
$blog_settings = get_option( 'spicecraft_blog_settings', array() );
if ( empty( $blog_settings['hero_title'] ) ) {
	$blog_settings['hero_title']              = 'Spice Knowledge & Market Intelligence';
	$blog_settings['hero_subtitle']           = 'Expert analysis on cryogenic processing, agricultural supply chains, ASTA quality benchmarks, and international spice export trends.';
	$blog_settings['show_featured_banner']    = 1;
	$blog_settings['show_reading_time']       = 1;
	$blog_settings['show_author']             = 1;
	$blog_settings['show_date']               = 1;
	$blog_settings['show_related_posts']      = 1;
	$blog_settings['enable_social_share']     = 1;
	$blog_settings['lead_cta_heading']        = 'Need Custom Milling or Bulk Specifications?';
	$blog_settings['lead_cta_text']           = 'Speak directly with our food scientists and master spice blenders to formulate proprietary blends with guaranteed purity certificates.';
	$blog_settings['lead_cta_button_text']    = 'Schedule Technical Consultation';
	$blog_settings['lead_cta_button_url']     = home_url( '/#contact' );
	update_option( 'spicecraft_blog_settings', $blog_settings );
	echo "2. Blog Settings saved.\n";
}

// 3. Create Categories
$categories_to_create = array(
	array(
		'name'        => 'Manufacturing & Processing',
		'slug'        => 'manufacturing-processing',
		'description' => 'Cryogenic milling, steam sterilization, cleanroom packaging, and modern plant technologies.',
	),
	array(
		'name'        => 'Quality & Food Safety',
		'slug'        => 'quality-food-safety',
		'description' => 'ASTA color value tests, pesticide residue analysis, FSSAI, BRCGS, and ISO laboratory compliance.',
	),
	array(
		'name'        => 'Industry Insights',
		'slug'        => 'industry-insights',
		'description' => 'Macro-economic trade flows, agricultural commodity market forecasts, and supply chain reports.',
	),
	array(
		'name'        => 'Sustainability & Sourcing',
		'slug'        => 'sourcing-sustainability',
		'description' => 'Direct farmer procurement, regenerative farming practices, and pesticide-free cultivation.',
	),
	array(
		'name'        => 'Culinary & Product Development',
		'slug'        => 'culinary-product-dev',
		'description' => 'Formulating custom spice rubs, savory seasoning systems, and industrial flavor profiles.',
	),
);

$cat_ids = array();
foreach ( $categories_to_create as $cat_data ) {
	$term = get_term_by( 'slug', $cat_data['slug'], 'category' );
	if ( ! $term ) {
		$res = wp_insert_term(
			$cat_data['name'],
			'category',
			array(
				'slug'        => $cat_data['slug'],
				'description' => $cat_data['description'],
			)
		);
		if ( ! is_wp_error( $res ) ) {
			$cat_ids[ $cat_data['slug'] ] = $res['term_id'];
			echo " - Created category: {$cat_data['name']} (ID {$res['term_id']})\n";
		}
	} else {
		$cat_ids[ $cat_data['slug'] ] = $term->term_id;
		echo " - Found existing category: {$cat_data['name']} (ID {$term->term_id})\n";
	}
}

// 4. Remove default 'Hello world!' or make it a draft
$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $hello ) {
	wp_trash_post( $hello->ID );
	echo "4. Trashed default 'Hello world!' post.\n";
}

// 5. Create rich spice manufacturing articles
$articles_data = array(
	array(
		'title'        => 'Cryogenic Grinding vs Traditional Hammer Milling: Preserving Volatile Spice Oils',
		'slug'         => 'cryogenic-grinding-vs-traditional-hammer-milling',
		'category'     => 'manufacturing-processing',
		'featured'     => 1,
		'subtitle'     => 'How low-temperature processing protects volatile aromatic compounds and maximizes shelf stability for industrial food brands.',
		'tags'         => array( 'Cryogenic Grinding', 'Milling Technology', 'Essential Oils', 'Food Processing' ),
		'image_id'     => 32, // Facility cleanroom
		'content'      => '<h2>The Physics of Heat Degradation in Industrial Milling</h2>
<p>In conventional high-speed hammer milling, mechanical friction can easily drive grinding chamber temperatures upwards of 60°C to 80°C. For oil-dense botanicals like black pepper, cardamom, and clove, this thermal spike causes rapid vapor loss of delicate terpenes, pinene, and piperine.</p>

<p>Cryogenic grinding addresses this fundamental vulnerability by pre-cooling the raw spices using liquid nitrogen at -196°C prior to milling. At sub-zero temperatures, the spice material becomes glass-brittle, fragmenting cleanly without generating localized heat.</p>

<blockquote>
	"Cryogenic milling retains up to 98% of volatile essential oils compared to only 55-65% in ambient ambient hammer mills. The sensory difference in final packaged food formulations is night and day."
</blockquote>

<h2>Comparative Analysis: Technical Performance Metrics</h2>
<table>
	<thead>
		<tr>
			<th>Parameter</th>
			<th>Traditional Hammer Milling</th>
			<th>SpiceCraft Cryogenic Milling</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Chamber Temperature</td>
			<td>55°C – 82°C</td>
			<td>-15°C – 0°C Controlled</td>
		</tr>
		<tr>
			<td>Volatile Oil Retention</td>
			<td>55% – 68%</td>
			<td>94% – 98.5%</td>
		</tr>
		<tr>
			<td>Particle Uniformity (Mesh)</td>
			<td>Variable (High dust fines)</td>
			<td>Precision 40–100 Mesh Spectrum</td>
		</tr>
		<tr>
			<td>Oxidative Degradation</td>
			<td>Moderate to High</td>
			<td>Near Zero (Inert N2 Blanket)</td>
		</tr>
	</tbody>
</table>

<h2>Benefits for Commercial FMCG and Seasoning Manufacturers</h2>
<p>For industrial seasoning manufacturers, private label blenders, and snack food formulators, cryogenically ground powders provide higher aroma intensity per kilogram. This allows product developers to reduce total spice loading by 12% to 18% while achieving an equivalent or superior organoleptic profile on extruded snacks, cured meats, and ready meals.</p>

<h3>Key Formulation Advantages:</h3>
<ul>
	<li><strong>Extended Shelf Life:</strong> Lower initial oxidation delivers up to 24 months of color and aroma retention.</li>
	<li><strong>Instant Solubility:</strong> Micro-fractured uniform particles disperse effortlessly in brines, gravies, and oil suspensions.</li>
	<li><strong>Zero Chemical Additives:</strong> 100% mechanical preservation with no synthetic stabilizers or anti-caking agents needed.</li>
</ul>

<p>At SpiceCraft, our state-of-the-art cryogenic milling line operates under positive-pressure HEPA filtration, ensuring every metric ton delivered to global ports exceeds international standards.</p>',
	),
	array(
		'title'        => 'ASTA Color Value & Curcumin Standardization in Turmeric Powders',
		'slug'         => 'asta-color-value-and-curcumin-standardization',
		'category'     => 'quality-food-safety',
		'featured'     => 1,
		'subtitle'     => 'Understanding HPLC testing protocols, spectrophotometric benchmarks, and global compliance for turmeric exports.',
		'tags'         => array( 'Turmeric', 'Curcumin', 'Quality Control', 'ASTA Standards', 'Laboratory Testing' ),
		'image_id'     => 18, // Turmeric pack
		'content'      => '<h2>The Science of Color Standardization in Turmeric</h2>
<p>For international buyers in confectionery, dairy, and culinary sectors, turmeric is valued not only as a warm, earthy flavor foundation but as a high-performance natural coloring agent. Quantifying this characteristic requires standardized testing under American Spice Trade Association (ASTA) guidelines.</p>

<p>ASTA Method 18.0 measures extractable color value via solvent extraction and spectrophotometric absorption at 425 nm. A higher ASTA score directly correlates with intense, vibrant yellow-orange hue retention in aqueous and fat-based applications.</p>

<h2>Curcumin Concentration: Grade Classifications</h2>
<p>While standard commercial turmeric offers between 2.0% and 3.0% curcuminoids, SpiceCraft sources specialized cultivars from the fertile soils of Salem, Erode, and Lakadong:</p>

<ul>
	<li><strong>Commercial Processing Grade:</strong> 2.5% – 3.2% Curcumin | ASTA 80–95. Ideal for blended curry powders and industrial snack coatings.</li>
	<li><strong>Premium Export Grade:</strong> 3.5% – 4.5% Curcumin | ASTA 100–120. Optimized for European food manufacturers and premium retail packing.</li>
	<li><strong>Lakadong High-Potency Grade:</strong> 6.5% – 8.0% Curcumin | ASTA 135+. Highly sought after by nutraceutical and dietary supplement laboratories.</li>
</ul>

<blockquote>
	"Every container load leaving our factory is accompanied by a batch-specific Certificate of Analysis validated via High-Performance Liquid Chromatography (HPLC)."
</blockquote>

<h2>Comprehensive Screening Protocols</h2>
<p>Beyond color and curcumin, food safety regulations in the EU, US, and Japan demand stringent screening for heavy metals (specifically lead chromate adulteration, which has plagued unverified markets). SpiceCraft utilizes Inductively Coupled Plasma Mass Spectrometry (ICP-MS) to guarantee zero lead detection below 0.1 ppm, fully complying with US FDA and EU Commission Regulation (EU) 2023/915.</p>',
	),
	array(
		'title'        => 'Global Black Pepper Market Trends: 2026 Procurement Guide for Food Processors',
		'slug'         => 'global-black-pepper-market-trends-2026-procurement-guide',
		'category'     => 'industry-insights',
		'featured'     => 0,
		'subtitle'     => 'Supply chain forecasts, Malabar crop cycles, and sea freight logistics shaping whole tellicherry and ground pepper procurement.',
		'tags'         => array( 'Black Pepper', 'Market Trends', 'Procurement', 'Supply Chain', 'Commodity Trading' ),
		'image_id'     => 31, // Master showcase
		'content'      => '<h2>State of the Global Black Pepper Trade</h2>
<p>The global black pepper (<em>Piper nigrum</em>) market has experienced unprecedented dynamic shifts over the past 24 months. Unseasonal monsoon patterns across key growing regions in Vietnam, Indonesia, and southern India have constrained top-grade Tellicherry berry availability, elevating farm-gate prices and highlighting the necessity of long-term contract farming.</p>

<h2>Understanding Pepper Density (GL Levels)</h2>
<p>In wholesale transactions, bulk black pepper is priced and specified by bulk density measured in Grams per Liter (G/L):</p>
<ul>
	<li><strong>500 G/L FAQ:</strong> Fair Average Quality, predominantly used for oil oleoresin extraction.</li>
	<li><strong>550 G/L Semi-Garbled:</strong> Suitable for coarse butcher grind and industrial meat processing.</li>
	<li><strong>570 – 600 G/L Fully Garbled (TGSEB):</strong> Tellicherry Garbled Special Extra Bold berries, hand-sorted for the highest essential oil content and luxury table packaging.</li>
</ul>

<p>SpiceCraft works directly with agricultural cooperatives across Wayanad and Idukki to lock in forward supply agreements, shielding our commercial clients from mid-season spot-market volatility.</p>',
	),
	array(
		'title'        => 'Steam Sterilization Protocols: Eliminating Pathogens Without Chemical Irradiation',
		'slug'         => 'steam-sterilization-protocols-pathogen-control',
		'category'     => 'quality-food-safety',
		'featured'     => 0,
		'subtitle'     => 'Ensuring Salmonella-free export consignments with continuous pressurized HTST steam technology.',
		'tags'         => array( 'Food Safety', 'Steam Sterilization', 'Salmonella', 'Export Standards', 'BRCGS' ),
		'image_id'     => 33, // Farm sourcing / clean facility
		'content'      => '<h2>The Imperative for Pathogen Elimination</h2>
<p>Whole and ground spices are natural agricultural commodities harvested directly from soil contact. Consequently, raw spices inherently carry background microbial loads, including spore-forming bacteria and potential pathogens such as <em>Salmonella</em> and <em>Bacillus cereus</em>.</p>

<p>While ethylene oxide (EtO) fumigation and gamma irradiation were historically utilized in certain jurisdictions, contemporary health-conscious markets—most notably the European Union—have banned EtO due to carcinogenic residue concerns.</p>

<h2>The High-Temperature Short-Time (HTST) Steam Solution</h2>
<p>SpiceCraft employs advanced continuous pressurized steam sterilization. The process exposes whole spices to dry saturated steam at 105°C – 120°C for precisely metered intervals of 15 to 45 seconds, followed immediately by rapid vacuum cooling and fluidized bed drying.</p>

<h3>Process Validation:</h3>
<ul>
	<li><strong>5-Log Reduction:</strong> Certified elimination of <em>Salmonella spp.</em> and coliforms.</li>
	<li><strong>Preserved Aesthetics:</strong> Short exposure windows ensure seed coat coloration and gloss remain unblemished.</li>
	<li><strong>Moisture Control:</strong> Finished moisture levels are tightly brought back under 10.0%, preventing mycotoxin generation.</li>
</ul>',
	),
	array(
		'title'        => 'Sustainable Direct-Farmer Partnerships: Building Resilient Agricultural Corridors',
		'slug'         => 'sustainable-direct-farmer-partnerships-resilient-supply-chains',
		'category'     => 'sourcing-sustainability',
		'featured'     => 0,
		'subtitle'     => 'How regenerative farming, fair price premiums, and digital batch traceability empower spice growers while securing clean raw materials.',
		'tags'         => array( 'Sustainability', 'Traceability', 'Fair Trade', 'Contract Farming', 'ESG' ),
		'image_id'     => 20, // Cumin seeds pack
		'content'      => '<h2>Beyond Transactional Procurement</h2>
<p>Traditional spice supply chains rely on fragmented tiers of local village middlemen, sub-agents, and auction yards. This opacity breeds inconsistent quality, high post-harvest handling losses, and an absence of pesticide provenance.</p>

<p>SpiceCraft has pioneered direct grower partnerships with over 1,200 smallholder spice farmers across Gujarat, Rajasthan, and Kerala. By establishing village collection hubs and providing agronomic soil-testing assistance, we eliminate exploitative intermediaries and deliver premium price payouts directly to farming households.</p>

<h2>Digital Traceability from Acre to Export Port</h2>
<p>Through our dedicated geo-tagging initiative, every shipment of cumin, coriander, and fenugreek is tagged with field-level origin coordinates. Buyers can trace their bulk container lots back to the exact cluster of farms that nurtured the harvest.</p>',
	),
);

$created_post_ids = array();
foreach ( $articles_data as $art ) {
	$existing = get_page_by_path( $art['slug'], OBJECT, 'post' );
	$post_data = array(
		'post_title'   => $art['title'],
		'post_name'    => $art['slug'],
		'post_content' => $art['content'],
		'post_excerpt' => $art['subtitle'],
		'post_status'  => 'publish',
		'post_type'    => 'post',
		'post_author'  => 1,
	);

	if ( $existing ) {
		$post_data['ID'] = $existing->ID;
		$post_id = wp_update_post( $post_data );
		echo " - Updated article: {$art['title']} (ID {$post_id})\n";
	} else {
		$post_id = wp_insert_post( $post_data );
		echo " - Created article: {$art['title']} (ID {$post_id})\n";
	}

	if ( ! is_wp_error( $post_id ) ) {
		$created_post_ids[] = $post_id;

		// Assign Category
		if ( isset( $cat_ids[ $art['category'] ] ) ) {
			wp_set_post_categories( $post_id, array( $cat_ids[ $art['category'] ] ) );
		}

		// Assign Tags
		wp_set_post_tags( $post_id, $art['tags'] );

		// Set Featured Image
		if ( ! empty( $art['image_id'] ) ) {
			set_post_thumbnail( $post_id, $art['image_id'] );
		}

		// Save Meta
		update_post_meta( $post_id, '_sc_post_is_featured', $art['featured'] ? 1 : 0 );
		update_post_meta( $post_id, '_sc_post_subtitle', $art['subtitle'] );
		$reading_time = function_exists( 'spicecraft_calculate_reading_time' ) ? spicecraft_calculate_reading_time( $art['content'] ) : 4;
		update_post_meta( $post_id, '_sc_post_reading_time', $reading_time );
	}
}

// 6. Setup curated related articles
if ( count( $created_post_ids ) >= 3 ) {
	// For post 0 (Cryogenic Grinding), relate to post 1 (ASTA/Turmeric) and post 3 (Steam sterilization)
	update_post_meta( $created_post_ids[0], '_sc_post_related_ids', array( $created_post_ids[1], $created_post_ids[3] ) );
	// For post 1, relate to post 0 and post 3
	update_post_meta( $created_post_ids[1], '_sc_post_related_ids', array( $created_post_ids[0], $created_post_ids[3] ) );
	echo "6. Curated related articles meta saved.\n";
}

// 7. Update Menus
$primary_menu = wp_get_nav_menu_object( 'primary-navigation' );
if ( $primary_menu ) {
	$menu_items = wp_get_nav_menu_items( $primary_menu->term_id );
	$has_blog = false;
	foreach ( $menu_items as $mi ) {
		if ( strpos( $mi->url, '/blog/' ) !== false || strpos( $mi->url, 'blog' ) !== false ) {
			$has_blog = true;
			break;
		}
	}
	if ( ! $has_blog ) {
		wp_update_nav_menu_item(
			$primary_menu->term_id,
			0,
			array(
				'menu-item-title'   => __( 'Blog & Insights', 'spicecraft' ),
				'menu-item-url'     => home_url( '/blog/' ),
				'menu-item-status'  => 'publish',
				'menu-item-position'=> 4,
			)
		);
		echo "7. Added 'Blog & Insights' to Primary Navigation.\n";
	} else {
		echo "7. 'Blog & Insights' already in Primary Navigation.\n";
	}
}

$footer_resources_menu = wp_get_nav_menu_object( 'footer-resources' );
if ( $footer_resources_menu ) {
	$f_items = wp_get_nav_menu_items( $footer_resources_menu->term_id );
	foreach ( $f_items as $fi ) {
		if ( $fi->title === 'Blog & Industry Insights' && strpos( $fi->url, '#blog' ) !== false ) {
			wp_update_nav_menu_item(
				$footer_resources_menu->term_id,
				$fi->ID,
				array(
					'menu-item-title'  => $fi->title,
					'menu-item-url'    => home_url( '/blog/' ),
					'menu-item-status' => 'publish',
				)
			);
			echo "7. Updated 'Blog & Industry Insights' in Footer Resources to link to /blog/.\n";
		}
	}
}

echo "=== BLOG SEEDING COMPLETE ===\n";
