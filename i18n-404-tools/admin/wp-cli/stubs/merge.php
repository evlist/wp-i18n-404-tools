<?php
/**
 * Classe stub Merge pour la compatibilité WP-CLI i18n.
 *
 * @package i18n-404-tools
 * @author  L'équipe i18n-404-tools
 * SPDX-FileCopyrightText: 2026 L'équipe i18n-404-tools
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace Gettext;

/**
 * Classe utilitaire pour la fusion de collections de traductions.
 * Fournit des constantes pour les options de fusion.
 */
class Merge {
	/** Option pour ajouter des entrées. */
	const ADD = 1;
	/** Option pour supprimer des entrées. */
	const REMOVE = 2;
	/** Option pour fusionner les commentaires de l'autre collection. */
	const COMMENTS_THEIRS = 4;
	/** Option pour fusionner les commentaires extraits de l'autre collection. */
	const EXTRACTED_COMMENTS_THEIRS = 8;
	/** Option pour fusionner les références de l'autre collection. */
	const REFERENCES_THEIRS = 16;
	/** Option pour écraser le domaine. */
	const DOMAIN_OVERRIDE = 32;
}
