<?php

namespace phloat\bridges\http_request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Pascal Muenst <dev@timesplinter.ch>
 * @copyright Copyright (c) 2015, TiMESPLiNTER Webdevelopment
 */
class PsrRequest extends Request implements RequestInterface
{
	/**
	 * Retrieves the HTTP protocol version as a string.
	 *
	 * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
	 *
	 * @return string HTTP protocol version.
	 */
	public function getProtocolVersion()
	{
		$protocol = $this->server->get('SERVER_PROTOCOL');
		
		return substr($protocol, strpos($protocol, '/') + 1);
	}

	/**
	 * Return an instance with the specified HTTP protocol version.
	 *
	 * The version string MUST contain only the HTTP version number (e.g.,
	 * "1.1", "1.0").
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * new protocol version.
	 *
	 * @param string $version HTTP protocol version
	 *
	 * @return self
	 */
	public function withProtocolVersion($version)
	{
		$this->server->set('SERVER_PROTOCOL', 'HTTP/' . $version);
		
		return $this;
	}

	/**
	 * Checks if a header exists by the given case-insensitive name.
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return bool Returns true if any header names match the given header
	 *     name using a case-insensitive string comparison. Returns false if
	 *     no matching header name is found in the message.
	 */
	public function hasHeader($name)
	{
		return $this->headers->has($name);
	}

	/**
	 * Return an instance with the provided header, replacing any existing
	 * values of any headers with the same case-insensitive name.
	 *
	 * While header names are case-insensitive, the casing of the header will
	 * be preserved by this function, and returned from getHeaders().
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * new and/or updated header and value.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @param string|string[] $value Header value(s).
	 *
	 * @return self
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function withHeader($name, $value)
	{
		$this->headers->set($name, $value);
		
		return $this;
	}

	/**
	 * Return an instance with the specified header appended with the
	 * given value.
	 *
	 * Existing values for the specified header will be maintained. The new
	 * value(s) will be appended to the existing list. If the header did not
	 * exist previously, it will be added.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * new header and/or value.
	 *
	 * @param string $name Case-insensitive header field name to add.
	 * @param string|string[] $value Header value(s).
	 *
	 * @return self
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function withAddedHeader($name, $value)
	{
		$this->headers->set($name, $value);

		return $this;
	}
	
	/**
	 * Return an instance without the specified header.
	 *
	 * Header resolution MUST be done without case-sensitivity.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that removes
	 * the named header.
	 *
	 * @param string $name Case-insensitive header field name to remove.
	 *
	 * @return self
	 */
	public function withoutHeader($name)
	{
		$this->headers->remove($name);
		
		return $this;
	}

	/**
	 * Gets the body of the message.
	 *
	 * @return StreamInterface Returns the body as a stream.
	 */
	public function getBody()
	{
		
	}

	/**
	 * Return an instance with the specified message body.
	 *
	 * The body MUST be a StreamInterface object.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return a new instance that has the
	 * new body stream.
	 *
	 * @param StreamInterface $body Body.
	 *
	 * @return self
	 * @throws \InvalidArgumentException When the body is not valid.
	 */
	public function withBody(StreamInterface $body)
	{
		// TODO: Implement withBody() method.
	}

	/**
	 * Extends MessageInterface::getHeaders() to provide request-specific
	 * behavior.
	 *
	 * Retrieves all message headers.
	 *
	 * This method acts exactly like MessageInterface::getHeaders(), with one
	 * behavioral change: if the Host header has not been previously set, the
	 * method MUST attempt to pull the host component of the composed URI, if
	 * present.
	 *
	 * @see MessageInterface::getHeaders()
	 * @see UriInterface::getHost()
	 * @return array Returns an associative array of the message's headers. Each
	 *     key MUST be a header name, and each value MUST be an array of strings.
	 */
	public function getHeaders()
	{
		return $this->headers->all();
	}

	/**
	 * Extends MessageInterface::getHeader() to provide request-specific
	 * behavior.
	 *
	 * This method acts exactly like MessageInterface::getHeader(), with
	 * one behavioral change: if the Host header is requested, but has
	 * not been previously set, the method MUST attempt to pull the host
	 * component of the composed URI, if present.
	 *
	 * @see MessageInterface::getHeader()
	 * @see UriInterface::getHost()
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return string[] An array of string values as provided for the given
	 *    header. If the header does not appear in the message, this method MUST
	 *    return an empty array.
	 */
	public function getHeader($name)
	{
		return (array)$this->headers->get($name, array());
	}

	/**
	 * Extends MessageInterface::getHeaderLines() to provide request-specific
	 * behavior.
	 *
	 * This method returns all of the header values of the given
	 * case-insensitive header name as a string concatenated together using
	 * a comma.
	 *
	 * This method acts exactly like MessageInterface::getHeaderLines(), with
	 * one behavioral change: if the Host header is requested, but has
	 * not been previously set, the method MUST attempt to pull the host
	 * component of the composed URI, if present.
	 *
	 * @see MessageInterface::getHeaderLine()
	 * @see UriInterface::getHost()
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return string|null A string of values as provided for the given header
	 *    concatenated together using a comma. If the header does not appear in
	 *    the message, this method MUST return a null value.
	 */
	public function getHeaderLine($name)
	{
		return implode(',', $this->getHeader($name));
	}

	/**
	 * Retrieves the message's request target.
	 *
	 * Retrieves the message's request-target either as it will appear (for
	 * clients), as it appeared at request (for servers), or as it was
	 * specified for the instance (see withRequestTarget()).
	 *
	 * In most cases, this will be the origin-form of the composed URI,
	 * unless a value was provided to the concrete implementation (see
	 * withRequestTarget() below).
	 *
	 * If no URI is available, and no request-target has been specifically
	 * provided, this method MUST return the string "/".
	 *
	 * @return string
	 */
	public function getRequestTarget()
	{
		// TODO: Implement getRequestTarget() method.
	}

	/**
	 * Return an instance with the specific request-target.
	 *
	 * If the request needs a non-origin-form request-target — e.g., for
	 * specifying an absolute-form, authority-form, or asterisk-form —
	 * this method may be used to create an instance with the specified
	 * request-target, verbatim.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request target.
	 *
	 * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
	 *     request-target forms allowed in request messages)
	 *
	 * @param mixed $requestTarget
	 *
	 * @return self
	 */
	public function withRequestTarget($requestTarget)
	{
		// TODO: Implement withRequestTarget() method.
	}

	/**
	 * Return an instance with the provided HTTP method.
	 *
	 * While HTTP method names are typically all uppercase characters, HTTP
	 * method names are case-sensitive and thus implementations SHOULD NOT
	 * modify the given string.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request method.
	 *
	 * @param string $method Case-insensitive method.
	 *
	 * @return self
	 * @throws \InvalidArgumentException for invalid HTTP methods.
	 */
	public function withMethod($method)
	{
		// TODO: Implement withMethod() method.
	}

	/**
	 * Returns an instance with the provided URI.
	 *
	 * This method will update the Host header of the returned request by
	 * default if the URI contains a host component. If the URI does not
	 * contain a host component, any pre-existing Host header will be carried
	 * over to the returned request.
	 *
	 * You can opt-in to preserving the original state of the Host header by
	 * setting `$preserveHost` to `true`. When `$preserveHost` is set to
	 * `true`, the returned request will not update the Host header of the
	 * returned message -- even if the message contains no Host header. This
	 * means that a call to `getHeader('Host')` on the original request MUST
	 * equal the return value of a call to `getHeader('Host')` on the returned
	 * request.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * new UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 *
	 * @param UriInterface $uri New request URI to use.
	 * @param bool $preserveHost Preserve the original state of the Host header.
	 *
	 * @return self
	 */
	public function withUri(UriInterface $uri, $preserveHost = false)
	{
		// TODO: Implement withUri() method.
	}
}