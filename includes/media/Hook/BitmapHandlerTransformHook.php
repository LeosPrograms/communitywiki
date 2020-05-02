<?php

namespace MediaWiki\Hook;

use BitmapHandler;
use File;
use MediaTransformOutput;

/**
 * @stable for implementation
 * @ingroup Hooks
 */
interface BitmapHandlerTransformHook {
	/**
	 * This hook is called before a file is transformed, giving extensions the
	 * possibility to transform it themselves.
	 *
	 * @since 1.35
	 *
	 * @param BitmapHandler $handler
	 * @param File $image
	 * @param array &$scalerParams Array with scaler parameters
	 * @param null|MediaTransformOutput &$mto
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onBitmapHandlerTransform( $handler, $image, &$scalerParams,
		&$mto
	);
}
