const { defineConfig } = require("@vue/cli-service");
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "",
  devServer: {
    open: true,
    port: "6431",
  },
  outputDir: "../static/",
});
