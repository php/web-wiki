/* Add buttons related to Markdown to the toolbar */

if (typeof window.toolbar !== 'undefined') {
    toolbar[toolbar.length] = {
        type: "insert",
        title: "Markdown Doctype",
        icon: "../../plugins/commonmark/images/markdown.png",
        key: "",
        insert: "<!DOCTYPE markdown>",
        block: "true"
    };
    toolbar[toolbar.length] = {
        type: "picker",
        title: "Markdown syntax",
        icon: "../../plugins/commonmark/images/markdown_white.png",
	class: "pk_hl",
        key: "",
        list: [
	    {
		type: "format",
		title: toolbar[0].title,
		icon: "bold.png",
		key: "",
		open: "**",
		close: "**",
		block: "false"
	    },
	    {
		type: "format",
		title: toolbar[1].title,
		icon: "italic.png",
		key: "",
		open: "*",
		close: "*",
		block: "false"
	    },
	    {
		type: "format",
		title: toolbar[3].title,
		icon: "mono.png",
		key: "",
		open: "`",
		close: "`",
		block: "false"
	    },
	    {
		type: "format",
		title: toolbar[4].title,
		icon: "bold.png",
		key: "",
		open: "~~",
		close: "~~",
		block: "false"
	    }
	]
    };
}
