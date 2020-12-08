<?php

use MediaWiki\MediaWikiServices;

class SpecialCiteThisPage extends FormSpecialPage {

	/**
	 * @var Parser
	 */
	private $citationParser;

	/**
	 * @var Title|bool
	 */
	protected $title = false;

	public function __construct() {
		parent::__construct( 'CiteThisPage' );
	}

	/**
	 * @param string $par
	 */
	public function execute( $par ) {
		$this->setHeaders();
		$this->addHelpLink( 'Extension:CiteThisPage' );
		parent::execute( $par );
		if ( $this->title instanceof Title ) {
			$id = $this->getRequest()->getInt( 'id' );
			$this->showCitations( $this->title, $id );
		}
	}

	protected function alterForm( HTMLForm $form ) {
		$form->setMethod( 'get' );
		$form->setFormIdentifier( 'titleform' );
	}

	protected function getFormFields() {
		if ( isset( $this->par ) ) {
			$default = $this->par;
		} else {
			$default = '';
		}
		return [
			'page' => [
				'name' => 'page',
				'type' => 'title',
				'default' => $default,
				'label-message' => 'citethispage-change-target'
			]
		];
	}

	public function onSubmit( array $data ) {
		// GET forms are "submitted" on every view, so check
		// that some data was put in for page
		if ( strlen( $data['page'] ) ) {
			$this->title = Title::newFromText( $data['page'] );
		}
		return true;
	}

	/**
	 * Return an array of subpages beginning with $search that this special page will accept.
	 *
	 * @param string $search Prefix to search for
	 * @param int $limit Maximum number of results to return (usually 10)
	 * @param int $offset Number of results to skip (usually 0)
	 * @return string[] Matching subpages
	 */
	public function prefixSearchSubpages( $search, $limit, $offset ) {
		return $this->prefixSearchString( $search, $limit, $offset );
	}

	protected function getGroupName() {
		return 'pagetools';
	}

	private function showCitations( Title $title, $revId ) {
		if ( !$revId ) {
			$revId = $title->getLatestRevID();
		}

		$out = $this->getOutput();

		$revTimestamp = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getTimestampFromId( $revId );

		if ( !$revTimestamp ) {
			$out->wrapWikiMsg( '<div class="errorbox">$1</div>',
				[ 'citethispage-badrevision', $title->getPrefixedText(), $revId ] );
			return;
		}

		$parserOptions = $this->getParserOptions();
		// Set the overall timestamp to the revision's timestamp
		$parserOptions->setTimestamp( $revTimestamp );

		$parser = $this->getParser();
		// Register our <citation> tag which just parses using a different
		// context
		$parser->setHook( 'citation', [ $this, 'citationTag' ] );
		// Also hold on to a separate Parser instance for <citation> tag parsing
		// since we can't parse in a parse using the same Parser
		$this->citationParser = $this->getParser();

		$ret = $parser->parse(
			$this->getContentText(),
			$title,
			$parserOptions,
			/* $linestart = */ false,
			/* $clearstate = */ true,
			$revId
		);

		$this->getOutput()->addModuleStyles( 'ext.citeThisPage' );
		$this->getOutput()->addParserOutputContent( $ret, [
			'enableSectionEditLinks' => false,
		] );
	}

	/**
	 * @return Parser
	 */
	private function getParser() {
		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		return $parserFactory->create();
	}

	/**
	 * Get the content to parse
	 *
	 * @return string
	 */
	private function getContentText() {
		$msg = $this->msg( 'citethispage-content' )->inContentLanguage()->plain();
		if ( $msg == '' ) {
			# With MediaWiki 1.20 the plain text files were deleted
			# and the text moved into SpecialCite.i18n.php
			# This code is kept for b/c in case an installation has its own file "citethispage-content-xx"
			# for a previously not supported language.
			global $wgLanguageCode;
			$dir = __DIR__ . '/../';
			$code = MediaWikiServices::getInstance()->getContentLanguage()
				->lc( $wgLanguageCode );
			if ( file_exists( "${dir}citethispage-content-$code" ) ) {
				$msg = file_get_contents( "${dir}citethispage-content-$code" );
			} elseif ( file_exists( "${dir}citethispage-content" ) ) {
				$msg = file_get_contents( "${dir}citethispage-content" );
			}
		}

		return $msg;
	}

	/**
	 * Get the common ParserOptions for both parses
	 *
	 * @return ParserOptions
	 */
	private function getParserOptions() {
		$parserOptions = ParserOptions::newFromUser( $this->getUser() );
		$parserOptions->setDateFormat( 'default' );
		$parserOptions->setInterfaceMessage( true );
		return $parserOptions;
	}

	/**
	 * Implements the <citation> tag.
	 *
	 * This is a hack to allow content that is typically parsed
	 * using the page's timestamp/pagetitle to use the current
	 * request's time and title
	 *
	 * @param string $text
	 * @param array $params
	 * @param Parser $parser
	 * @return string
	 */
	public function citationTag( $text, $params, Parser $parser ) {
		$parserOptions = $this->getParserOptions();

		$ret = $this->citationParser->parse(
			$text,
			$this->getPageTitle(),
			$parserOptions,
			/* $linestart = */ false
		);

		return Parser::stripOuterParagraph( $ret->getText( [
			'enableSectionEditLinks' => false,
			// This will be inserted into the output of another parser, so there will actually be a wrapper
			'unwrap' => true,
			'wrapperDivClass' => '',
		] ) );
	}

	protected function getDisplayFormat() {
		return 'ooui';
	}

	public function requiresUnblock() {
		return false;
	}

	public function requiresWrite() {
		return false;
	}
}
