<!--

/*
  test sample for termlib.js and termlib_parser.js

  (c) Norbert Landsteiner 2005-2010
  mass:werk - media environments
  <http://www.masswerk.at>

*/

var term, parser;

var helpPage=[
	'%CS%+r Terminal Help %-r%n',
	'  This is just a sample to demonstrate command line parsing.',
	' ',
	'  Use one of the following commands:',
	'     clear [-a] .......... clear the terminal',
	'                           option "a" also removes the status line',
	'     number -n<value> .... return value of option "n" (test for options)',
	'     repeat -n<value> .... repeats the first argument n times (another test)',
	'     dump ................ lists all arguments and alphabetic options',
	'     login <username> .... sample login (test for raw mode)',
	'     exit ................ close the terminal (same as <ESC>)',
	'     help ................ show this help page',
	' ',
	'  other input will be echoed to the terminal as a list of parsed arguments',
	'  in the format <argument index> <quoting level> "<parsed value>".',
	' '
];

function termOpen() {
	if (!term || term.losed) {
		term=new Terminal(
			{
				x: 220,
				y: 70,
				termDiv: 'termDiv',
				ps: '[guest]$',
				initHandler: termInitHandler,
				handler: commandHandler,
				exitHandler: termExitHandler,
				cols: 160,
				crsrBlinkMode: true
			}
		);
		if (term) term.open();
		parser=new Parser();
	}
	else {
		term.focus();
	}
}

function termExitHandler() {
	//Does nothing for the moment
}

function termInitHandler() {
	// output a start up screen
	this.write(
		[
			"Welcome to the Solid Rock Terminal....",
			"I am Peter Hawke, a first generation Database AI",
			"Please login:",
			'%n'
		]
	);
	// set a double status line
	/*this.statusLine('', 8,2); // just a line of strike
	this.statusLine(' +++ This is just a test sample for command parsing. Type "help" for help. +++');
	this.maxLines -= 2;*/
	// and leave with prompt
	this.prompt();
}

function commandHandler() {
	this.newLine();
	// check for raw mode first (should not be parsed)
	if (this.rawMode) {
		if (this.env.getPassword) {
			// sample password handler (lineBuffer == stored username ?)
			if (this.lineBuffer == "janus key") {
				this.user = this.env.username;
				this.ps = '['+this.user+']>';
				this.type('Welcome '+this.user+', you are now logged on to the system.');
			}
			else {
				this.type('Sorry.');
			}
			this.env.username = '';
			this.env.getPassword = false;
		}
		// leave in normal mode
		this.rawMode = false;
		this.prompt();
		return;
	}
	// normal command parsing
	// just call the termlib_parser with a reference of the calling Terminal instance
	// parsed arguments will be imported in this.argv,
	// quoting levels per argument in this.argQL (quoting character or empty)
	// cursor for arguments is this.argc (used by Parser.getopt)
	// => see 'termlib_parse.js' for configuration and details
	parser.parseLine(this);
	if (this.argv.length == 0) {
		// no commmand line input
	}
	else if (this.argQL[0]) {
	    // first argument quoted -> error
		this.write("Syntax error: first argument quoted.");
	}
	else {
		var cmd = this.argv[this.argc++];
		/*
		  process commands now
		  1st argument: this.argv[this.argc]
		*/
		switch (cmd) {
			case 'help':
				this.write(helpPage);
				break;
			case 'clear':
				// get options
				var opts = parser.getopt(this, 'aA');
				if (opts.a) {
					// discard status line on opt "a" or "A"
					this.maxLines = this.conf.rows;
				}
				this.clear();
				break;
			case 'number':
				// test for value options
				var opts = parser.getopt(this, 'n');
				if (opts.illegals.length) {
					this.type('illegal option. usage: number -n<value>');
				}
				else if ((opts.n) && (opts.n.value != -1)) {
					this.type('option value: '+opts.n.value);
				}
				else {
					this.type('usage: number -n<value>');
				}
				break;
			case 'repeat':
				// another test for value options
				var opts = parser.getopt(this, 'n');
				if (opts.illegals.length) {
					this.type('illegal option. usage: repeat -n<value> <string>');
				}
				else if ((opts.n) && (opts.n.value != -1)) {
					// first normal argument is again this.argv[this.argc]
					var s = this.argv[this.argc];
					if (typeof s != 'undefined') {
						// repeat this string n times
						var a = [];
						for (var i=0; i<opts.n.value; i++) a[a.length] = s;
						this.type(a.join(' '));
					}
				}
				else {
					this.type('usage: repeat -n<value> <string>');
				}
				break;
			case 'login':
				// sample login (test for raw mode)
				if ((this.argc == this.argv.length) || (this.argv[this.argc] == '')) {
					this.type('usage: login <username>');
				}
				else {
					this.env.getPassword = true;
					this.env.username = this.argv[this.argc];
					this.type('password: ');
					// exit in raw mode (blind input)
					this.rawMode = true;
					this.lock = false;
					return;
				}
				break;
			case 'dump':
				var opts = parser.getopt(this, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
				for (var p in opts) {
					if (p == 'illegals') {
						if (opts.illegals.length) {
							this.type('  illegal options: "'+opts.illegals.join('", "')+'"');
							this.newLine();
						}
					}
					else {
						this.type('  option "'+p+'" value '+opts[p].value);
						this.newLine();
					}
				}
				while (this.argc<this.argv.length) {
					var ql = this.argQL[this.argc] || '-';
					this.type('  argument [QL: '+ql+'] "'+this.argv[this.argc++]+'"');
					this.newLine();
				}
				break;
			case 'exit':
				this.close();
				return;
			default:
				// for test purpose just output argv as list
				// assemble a string of style-escaped lines and output it in more-mode
				s=' INDEX  QL  ARGUMENT%n';
				for (var i=0; i<this.argv.length; i++) {
					s += this.globals.stringReplace('%', '%%',
							this.globals.fillLeft(i, 6) +
							this.globals.fillLeft((this.argQL[i])? this.argQL[i]:'-', 4) +
							'  "' + this.argv[i] + '"'
						) + '%n';
				}
				this.write(s, 1);
				return;
		}
	}
	this.prompt();
}

