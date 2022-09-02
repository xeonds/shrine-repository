const { defineConfig } = require("@vue/cli-service");
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "static/",
  devServer: {
    open: true,
    host: "localhost",
    port: "6431",
  },
  outputDir: "../static/",
});
