/// <reference types="vitest" />
import UnoCSS from "unocss/vite";
import Vue from "@vitejs/plugin-vue";
import { defineConfig } from "vite";

export default defineConfig({
  plugins: [UnoCSS(), Vue()],
  test: {
    environment: "happy-dom"
  }
});
