<?php

// SPDX-FileCopyrightText: 2025, 2026 Eric van der Vlist <vdv@dyomedea.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

/**
 * PHP string syntaxes test fixture - heredoc and nowdoc.
 *
 * @package I18n_404_Tools_Tests
 */

// Heredoc syntax (variables are interpolated).
$text = <<<EOT
This is a heredoc string
EOT;

// Heredoc with translation function.
__( <<<EOT
Heredoc translatable string
EOT
, 'testdomain' );

// Nowdoc syntax (variables NOT interpolated, like single quotes).
$text = <<<'EOT'
This is a nowdoc string
EOT;

// Nowdoc with translation function.
__( <<<'EOT'
Nowdoc translatable string
EOT
, 'testdomain' );

// Heredoc with special characters.
__( <<<EOT
Heredoc with "quotes" and 'apostrophes'
EOT
, 'testdomain' );

// Nowdoc with special characters.
__( <<<'EOT'
Nowdoc with "quotes" and 'apostrophes'
EOT
, 'testdomain' );

// Multiline heredoc.
__( <<<EOT
This is a multiline
heredoc string that spans
multiple lines
EOT
, 'testdomain' );

// Multiline nowdoc.
__( <<<'EOT'
This is a multiline
nowdoc string that spans
multiple lines
EOT
, 'testdomain' );
