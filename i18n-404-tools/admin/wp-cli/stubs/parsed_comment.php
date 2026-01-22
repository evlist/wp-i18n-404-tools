<?php
/**
 * Classe stub ParsedComment pour la compatibilité WP-CLI i18n.
 *
 * @package i18n-404-tools
 * @author  L'équipe i18n-404-tools
 * SPDX-FileCopyrightText: 2026 L'équipe i18n-404-tools
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Gettext\Utils;

/**
 * Classe représentant un commentaire extrait et parsé.
 * Utilisée pour stocker et manipuler les commentaires PO.
 */
class ParsedComment {
	/**
	 * Commentaire extrait du fichier PO.
	 *
	 * @var string
	 */
	private $comment;

	/**
	 * Constructeur de la classe ParsedComment.
	 * Initialise le commentaire extrait.
	 *
	 * @param string $comment Commentaire extrait.
	 */
	public function __construct( $comment ) {
		$this->comment = $comment;
	}

	/**
	 * Retourne le commentaire extrait.
	 *
	 * @return string Commentaire extrait.
	 */
	public function get_comment() {
		return $this->comment;
	}
}
