const { defineConfig } = require("@vue/cli-service");
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "static/", // build时记得改成"static/" ，开发时记得改成 ""
  devServer: {
    open: true,
    host: "localhost",
    port: "6431",
    proxy: {
      "http://localhost:6431/": {
        target: "http://localhost/repo/",
        changeOrigin: true, // 是否允许跨越, 开发环境中使用
        // pathRewrite: {
        //   "^/api": "",
        // },
      },
    },
  },
  outputDir: "../static/",
});
