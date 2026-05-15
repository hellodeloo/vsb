<?php
/**
 * Template Name: Maintenance
 * Description: Page vide avec logo.
 */
$theme_uri = get_stylesheet_directory_uri();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="La gauche ecologiste et citoyenne">
	<title>Vivre Saint-Brieuc</title>
	<link rel="stylesheet" href="<?php echo esc_url( $theme_uri . '/assets/css/reset.css' ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $theme_uri . '/assets/css/styles.css' ); ?>">
	<link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
	<div style="max-width:1000px;margin:16px auto;">
		<h1>
			<span class="visually-hidden">Vivre Saint-Brieuc</span>
			<img src="<?php echo esc_url( $theme_uri . '/assets/images/logo_vivre_st_brieuc_2026_HD_RVB.png' ); ?>" alt="" class="img-fluid">
		</h1>
	</div>
	<footer>
		<h3>Retrouvez-nous sur les reseaux sociaux ou via notre formulaire de contact</h3>
		<ul>
			<li>
				<a href="https://www.instagram.com/vivre_saint_brieuc" target="_blank" rel="noopener noreferrer">
					<span class="visually-hidden">Retrouvez-nous sur Instagram</span>
					<i class="ri-instagram-line" aria-hidden="true"></i>
				</a>
			</li>
			<li>
				<a href="https://www.facebook.com/people/Vivre-Saint-Brieuc/61551022785564/" target="_blank" rel="noopener noreferrer">
					<span class="visually-hidden">Retrouvez-nous sur Facebook</span>
					<i class="ri-facebook-circle-line" aria-hidden="true"></i>
				</a>
			</li>
			<li>
				<a href="https://forms.sbc37.com/6916c6700be67270a92407bb/lTmDjbYZTIyc4Lsd93F6Bg/form.html" target="_blank" rel="noopener noreferrer">
					<span class="visually-hidden">Rejoignez-nous !</span>
					<i class="ri-news-fill" aria-hidden="true"></i>
				</a>
			</li>
		</ul>
	</footer>
</body>
</html>
