# Igo-php - PHP による形態素解析プログラム

## 1, 概要

これは「Igo - Java 形態素解析器」の PHP による実装です。Igo は、MeCab 由来の辞書フォーマットを用い、ほぼ MeCab と同様の結果を提供する形態素解析プログラムで。この Igo-php は、Igo と同様の形態素解析と分かち書きの機能を提供します。

## 2, インストール方法

composer で以下のコマンドを実行します。

```shell
$ composer require rebib/igo-php
```

## 3. 辞書の作成方法

Igo-php 自体は、辞書生成の機能を提供しません。従って、辞書生成に当たっては、本家の Igo を使用します。

これについての詳細は、
https://igo.osdn.jp/index.html#usage
をご覧いただくとして、簡単な手順のみを以下に示します。

- MeCab プロジェクトが配布している(もしくはそれと互換性のある)辞書を入手する。
  - https://sf.net/projects/mecab/files/mecab-ipadic/2.7.0-20070801/
  - https://osdn.jp/projects/mecab/releases/?package_id=3701
  - https://osdn.jp/projects/naist-jdic/releases/?package_id=7240
  - https://osdn.jp/projects/unidic/releases/
  - https://github.com/neologd/mecab-ipadic-neologd/releases
- Igo 本体を https://osdn.jp/projects/igo/releases/ から入手する。

これらを用いて

```shell
$ java -cp igo-0.4.5.jar net.reduls.igo.bin.BuildDic ipadic mecab-ipadic-2.7.0-20070801 EUC-JP
```

この手順でカレントディレクトリに生成された ipadic/ 以下が辞書本体です。
これを、適当なディレクトリにコピーして使用してください。

## 4. 使用方法

### a. 分かち書き

```php
<?php
require 'vendor/autoload.php';

$igo = new Igo\Tagger();
$result = $igo->wakati('すもももももももものうち');
print_r($result);
```

### b. 形態素解析

```php
<?php
require 'vendor/autoload.php';

$igo = new Igo\Tagger();
$result = $igo->parse('すもももももももものうち');
print_r($result);
```

単体で使用する場合と同様に、指定した文字列から適切なエンコードが判定出来ないような場合は、Igo クラスのコンストラクト時の output_encoding の値を変更し、出力エンコードを明示的に指定することで回避できます。

```php
$igo = new Igo\Tagger([
    'dict_dir'=>"/home/user/jdic",
    'output_encoding'=>'Shift_JIS'
]);
```

デフォルトでは UTF-8 を優先して認識します。

### 5. REDUCE モード

これは、実行時の使用メモリを調整するためのものです。
既定では REDUCE モード TRUE で動作します。この状態で動作する時、Igo-php は、解析時に辞書ファイルに対してダイレクトアクセスします。OFF にすると、コンストラクタの実行時に、辞書を内部メモリに貯め込みます。解析処理自体は若干早くなりますが、使用メモリは増えます。比較的大量のテキストデータをバッチ処理で処理しなければならないような場合は、OFF にするとよいでしょう（しかし、このようなケースであれば、本家の Igo を使う方が、圧倒的に高速です）。
REDUCE モードを OFF にするには、以下のようにコンストラクト時に、reduce_mode を追記します。

```php
$igo = new Igo\Tagger([
    'dict_dir'=>'/home/user/jdic',
    'reduce_mode'=>false
]);
```

なお、REDUCE モードを FALSE で使用する場合、memory_limit パラメータを適切に設定するなどの考慮が必要です。

```php
ini_set("memory_limit", "1073741824"); //1024^3
```

## 6. バイトオーダーについて

バイナリ辞書のバイトオーダーは、辞書を作成した環境によります。ビッグエンディアンな環境で生成された辞書はリトルエンディアンのプラットフォームでは使用できません。逆もまた然り。
Igo-php は、デフォルトでリトルエンディアン用の設定になっています。
Intel 系のプラットフォームであれば、このままでよいはずです。ビッグエンディアンのプラットフォームで利用する場合は、コンストラクト時に little_endian の値を変更してみてください。

```php
$igo = new Igo\Tagger(array(
    'dict_dir'=>"/home/user/jdic",
    'little_endian'=>true //true->Little endian, false->Big endian
));
```

## 7. ライセンス

MIT ライセンスで配布いたします。
詳しくは同梱の COPYING ファイルを参照のこと。なお、使用する辞書のライセンスに関しては、辞書配布元のそれに準ずることとします。

## 8. 連絡先

- igo-php-devel@lists.osdn.jp
- logue@hotmail.co.jp

## 9. 参考リンク

「Igo - Java 形態素解析器」 https://igo.osdn.jp/
