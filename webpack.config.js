const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
module.exports = {
  mode: 'development',
  entry: {
    'js/app' : './src/js/app.js',
    'js/inicio' : './src/js/inicio.js',
    'js/desarrollador/index' : './src/js/desarrollador/index.js',
    'js/aplicaciones/index' : './src/js/aplicaciones/index.js',
    'js/visitas/index' : './src/js/visitas/index.js',
    'js/comentarios/index' : './src/js/comentarios/index.js',
    'js/gerente/index' : './src/js/gerente/index.js',
    //===============================================================
    //==============================================================
    // CSS
    'css/aplicaciones/style' : './src/css/aplicaciones/style.css',
    'css/comentarios/style' : './src/css/comentarios/style.css',
    'css/desarrollador/style' : './src/css/desarrollador/style.css',
    'css/gerente/style' : './src/css/gerente/style.css',
  },
output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'public/build')
  },
  plugins: [
    new MiniCssExtractPlugin({
        filename: '[name].css'  
    })
  ],
  module: {
    rules: [
      {
        test: /\.(c|sc|sa)ss$/,
        use: [
            {
                loader: MiniCssExtractPlugin.loader
            },
            'css-loader',
            'sass-loader'
        ]
      },
      {
        test: /\.(png|svg|jpe?g|gif)$/,
        type: 'asset/resource',
      },
    ]
  }
};