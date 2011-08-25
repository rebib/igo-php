<?php

class Searcher {
	private $keySetSize;
	private $base;
	private $chck;
	private $begs;
	private $lens;
	private $tail;

	public function __construct($filePath) {
		$fmis = new FileMappedInputStream($filePath);

		$nodeSz = $fmis->getInt();
		$tindSz = $fmis->getInt();
		$tailSz = $fmis->getInt();

		$this->keySetSize = $tindSz;
		$this->begs = $fmis->getIntArrayInstance($tindSz);
		$this->base = $fmis->getIntArrayInstance($nodeSz);
		$this->lens = $fmis->getShortArrayInstance($tindSz);
		$this->chck = $fmis->getCharArrayInstance($nodeSz);
		$this->tail = $fmis->getString($tailSz);

		$fmis->close();
	}

	public function size() {
		return $this->keySetSize;
	}
	public static function ID($id) {
		return $id * -1 - 1;
	}

	public function search($key) {
		$key = mb_convert_encoding($key, IGO_DICTIONARY_ENCODING, Igo::$ENCODE);
		$node = $this->base->get(0);
		$in = new KeyStream($key);

		for ($code = $in->read();; $code = $in->read()) {
			$idx = $node + $code;
			$node = $this->base->get($idx);

			if ($this->chck->get($idx) == $code) {
				if ($node >= 0) {
					continue;
				} else {
					if ($in->eos() || $this->keyExists($in, $node)) {
						return self::ID($node);
					}
				}
			}
			return -1;
		}
	}

	public function eachCommonPrefix($key, $start, $fn) {
		$key = mb_convert_encoding($key, IGO_DICTIONARY_ENCODING, Igo::$ENCODE);
		$node = $this->base->get(0);
		$offset = 0;
		$in = new KeyStream($key, $start);

		for ($code = $in->read();; $code = $in->read(), $offset++) {
			$terminalIdx = $node + NODE_CHECK_TERMINATE_CODE;
			$c = $this->chck->get($terminalIdx);
			if (empty($c)) {
				$fn->call($start, $offset, self::ID($this->base->get($terminalIdx)));
				if (empty($code) || $code == NODE_CHECK_TERMINATE_CODE) {
					return;
				}
			}

			$cpd = $this->codePoints($code);
			$idx = $node + $cpd;
			$node = $this->base->get($idx);
			$c = $this->chck->get($idx);
			if ($c == $code) {
				if ($node >= 0) {
					continue;
				} else {
					$this->call_if_keyIncluding($in, $node, $start, $offset, $fn);
				}
			}
			return;
		}
	}

	public static function codePoints($str) {
		$ord = unpack('N', mb_convert_encoding($str, 'UCS-4BE', Igo::$ENCODE));
		return $ord[1];
	}

	private function call_if_keyIncluding($in, $node, $start, $offset, $fn) {
		$id = self::ID($node);
		if ($in->startsWith($this->tail, $this->begs->get($id), $this->lens->get($id))) {
			$fn->call($start, $offset + $this->lens->get($id) + 1, $id);
		}
	}

	private function keyExists($in, $node) {
		$id = self::ID($node);
		$s = KeyStream::mb_substr($this->tail, $this->begs->get($id), $this->lens->get($id), Igo::$ENCODE);
		return $in . rest() == $s;
	}

}
?>