var express = require('express');
var app = exports.app = express();
var fs = require('fs');

app.use(express.favicon());
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use('/ace', express.static(__dirname + '/ace'));
app.set('port', 3000);
app.locals.pretty = true;
app.use(express.errorHandler());
app.use(express.logger('dev'));

app.get('/edit', function(req, res) {
  fs.readFile('index.html', 'utf8', function (err, data) {
    if (err) throw err;
    res.set('Content-Type', 'text/html');
    res.send(data.toString());
  });
});

app.get('/get', function(req, res) {
  var filename = req.query['file'];
  if (!filename) throw new Error('No file provided');
  fs.readFile('../' + filename, 'utf8', function (err, data) {
    if (err) throw err;
    res.set('Content-Type', 'text/plain');
    res.send(data.toString());
  });
});

app.post('/put', function(req, res) {
  fs.writeFile('../' + req.body.file, req.body.data, 'utf8', function(err) {
    if (err) throw err;
    res.send('');
  });
});


app.get('/find-file', function(req, res) {
  fs.exists('../index.html', function(exists) {
    res.send(exists ? 'index.html' : '');
  });
});

app.all('*', function(req, res) {
  var file = req.route.params.toString();
  fs.exists('..' + file, function(exists) {
    if (!exists) return res.send(404, 'Page not found.');
    res.sendfile(file, { root: '..' }, function(err) {
      if (err) throw err;
    });
  });
});

app.listen(app.get('port'));
console.log('Listening on port ' + app.get('port'));
