<?php

declare( strict_types=1 );

namespace Blockify\Core\Services;

class Style {

	private $handle;

	private $src;

	private $inline;

	private $template_contains;

	public function __construct( string $handle ) {
		$this->handle = $handle;
	}

	public function src( string $src ): self {
		$this->src = $src;
		return $this;
	}

	public function inline( callable $inline ): self {
		$this->inline = $inline;
		return $this;
	}

	public function template_contains( ...$template_contains ): self {
		$this->template_contains = $template_contains;
		return $this;
	}


}
