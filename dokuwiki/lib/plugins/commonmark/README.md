Dokuwiki Commonmark Plugin
===========================

## Description
This is another plugin for parsing Commonmark / Markdown document in Dokuwiki.

While there are many Markdown plugins (for example, [markdownu](https://www.dokuwiki.org/plugin:markdowku), [mdpage](https://www.dokuwiki.org/plugin:mdpage)) available, this plugin processes Markdown text in different approach:

1. check that Markdown indicator ('\<!DOCTYPE markdown\>') is included on the document
2. if exists, parses entire document and renders to DW syntax
3. After pre-rendering, passes the result to DW parser and process as usual

If you want to parse all DW pages as Commonmark without specific doctype, you can enable `force_commonmark` option on config. But this action may conflit with existing document of DW markup, so please use it carefully.
## Compatibility
Commonmark plugin aims for complete compatiblity of Markdown in Dokuwiki. Most Markdown syntax have corresponding DW syntax, so it will work without problem; but in some cases, Markdown syntax do not matches DW specification one-by-one, or vice versa. Here is a list of known ambiguities between Commonmark and Dokuwiki, and its implements in the plugin:

- Since the release of "Jack Jackrun", DW have [completely disabled](https://www.dokuwiki.org/faq:html) embedding HTML code. [HTML blocks](https://spec.commonmark.org/0.30/#html-blocks) will be passed as-is, unless enabling [htmlok plugin](https://www.dokuwiki.org/plugin:htmlok); but it is recommended to use it in limited environments.
- If you have enabled htmlok plugin, adding `html` as [info string](https://spec.commonmark.org/0.28/#info-string) in [Fenced code blocks](https://spec.commonmark.org/0.30/#fenced-code-blocks) will parse HTML code inside the block to DW's [\<HTML\>](https://www.dokuwiki.org/wiki:syntax#embedding_html_and_php) block; In case of `nowiki`, `<nowiki>` syntax will be parsed; if `dokuwiki`, raw DW code will be passed. For example:

````
```dokuwiki
<sub>subscript</sub>
```
````

Commonmark plugin would conflit with other markdown-related plugins, including [Mdpage](https://www.dokuwiki.org/plugin:mdpage).

Due to the concept, section edit will not work, or at least recognize the section as broken snippet rather than expected. This bug will be fixed in later version.

## License

- icons: Apache License from https://www.iconbolt.com/
