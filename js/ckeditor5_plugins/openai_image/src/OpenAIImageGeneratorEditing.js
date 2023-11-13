import OpenAIImageGeneratorCommand from "./OpenAIImageGeneratorCommand";
import { Plugin } from 'ckeditor5/src/core';
import { toWidget, toWidgetEditable } from 'ckeditor5/src/widget';
import { Widget } from 'ckeditor5/src/widget';

export default class OpenAIImageGeneratorEditing extends Plugin {
  static get requires() {
    return [ Widget ];
  }

  init() {
    console.log( 'OpenAIImageGeneratorEditing#init() got called' );

    this.editor.commands.add( 'generateOpenaiImage', new OpenAIImageGeneratorCommand( this.editor ) );

  }

}
