module.exports = {
	mode: 'jit',
	purge: {
        enabled: true,
		content: [
			'./*.php',
			'./src/*.js'
		],
	},
	darkMode: false, //you can set it to media(uses prefers-color-scheme) or class(better for toggling modes via a button)
	theme: {
		extend: {},
	},
	variants: {},
	plugins: [],
}