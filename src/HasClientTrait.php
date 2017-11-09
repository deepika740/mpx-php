<?php

/**
 * @file
 * Contains Mpx\HasClientTrait.
 */

namespace Mpx;

use GuzzleHttp\Client as GuzzleClient;

trait HasClientTrait {

  /**
   * @var \Mpx\ClientInterface
   */
  protected $client;

  /**
   * Sets a HTTP client.
   *
   * @param \Mpx\ClientInterface $client
   *
   * @return static
   */
  public function setClient(ClientInterface $client) {
    $this->client = $client;
    return $this;
  }

  /**
   * @return \Mpx\ClientInterface $client
   */
  public function getClient() {
    if (!isset($this->client)) {
      $this->client = new Client(new GuzzleClient());
    }
    return $this->client;
  }

}
