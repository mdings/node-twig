var execPHP = require('exec-php');
var trim = require('trim');

var extend = function extend(obj, src) {
  Object.keys(src).forEach(function(key) {
    obj[key] = src[key];
  });
  return obj;
};

var twigOptions = {
  root: null,
  extensions: [],
  context: {}
};

exports.renderFile = function(entry, options, cb) {
  // Get the extension for the filename
  var ext = entry.split('.').pop();
  var phpFile = (ext == 'php') ? 'php/Vanilla.php' : 'php/Twig.php';

  // Merge the global options with the local ones.
  options = extend(twigOptions, options);

  execPHP(phpFile, null, function(error, php) {
    // Call the callback on error or the render function on success.
    error ? cb(error) : php.render(entry, options, function(error, stdout, output, printed) {
      // Call the callback with an error or the trimmed output.
      var output = (ext == 'php') ? printed : stdout;
      error ? cb(error) : cb(null, trim(output));
    });
  });
};

exports.createEngine = function (options) {
  // Merge the options with default options.
  twigOptions = extend(twigOptions, options);

  return exports.renderFile;
};

exports.__express = exports.renderFile;
