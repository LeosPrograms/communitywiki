<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace MediaWiki\Minerva\Menu\Entries;

use MessageLocalizer;
use SpecialPage;

final class LogOutMenuEntry extends SingleMenuEntry {
	/**
	 * @param MessageLocalizer $messageLocalizer
	 * @param array $authLinksQuery
	 * @param string $iconType 'before' or 'element'.
	 * @param string $classes CSS class names.
	 */
	public function __construct(
		MessageLocalizer $messageLocalizer, array $authLinksQuery, $iconType = 'before', $classes = ''
	) {
		$text = $messageLocalizer->msg( 'mobile-frontend-main-menu-logout' )->escaped();
		$url = SpecialPage::getTitleFor( 'Userlogout' )->getLocalURL( $authLinksQuery );
		parent::__construct( 'logout', $text, $url, true, null, $iconType, $classes );
	}
}
