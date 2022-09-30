const { defineConfig } = require("@vue/cli-service");
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "static/", // build时记得改成"static/" ，开发时记得改成 ""
  devServer: {
    open: true,
    host: "localhost",
    port: "6431",
  },
  outputDir: "../static/",
});
