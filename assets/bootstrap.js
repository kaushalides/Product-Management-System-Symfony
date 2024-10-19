import { Application } from "@hotwired/stimulus";
import { definitionsFromContext } from "@hotwired/stimulus-webpack-helpers";

const app = Application.start();
const context = require.context("./controllers", true, /\.[jt]sx?$/);
app.load(definitionsFromContext(context));
