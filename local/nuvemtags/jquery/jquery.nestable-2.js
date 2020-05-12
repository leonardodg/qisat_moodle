/*!
 * Nestable jQuery Plugin - Copyright (c) 2012 David Bushell - http://dbushell.com/
 * Dual-licensed under the BSD or MIT licenses
 */
;
(function($, window, document, undefined) {
	var hasTouch = 'ontouchstart' in window;

	/**
	 * Detect CSS pointer-events property events are normally disabled on the
	 * dragging element to avoid conflicts
	 * https://github.com/ausi/Feature-detection-technique-for-pointer-events/blob/master/modernizr-pointerevents.js
	 */
	var hasPointerEvents = (function() {
		var el = document.createElement('div'), docEl = document.documentElement;
		if (!('pointerEvents' in el.style)) {
			return false;
		}
		el.style.pointerEvents = 'auto';
		el.style.pointerEvents = 'x';
		docEl.appendChild(el);
		var supports = window.getComputedStyle
				&& window.getComputedStyle(el, '').pointerEvents === 'auto';
		docEl.removeChild(el);
		return !!supports;
	})();

	var eStart = hasTouch ? 'touchstart' : 'mousedown', eMove = hasTouch ? 'touchmove'
			: 'mousemove', eEnd = hasTouch ? 'touchend' : 'mouseup';
	eCancel = hasTouch ? 'touchcancel' : 'mouseup';

	var defaults = {
		listNodeName : 'ol',
		itemNodeName : 'li',
		rootClass : 'dd',
		listClass : 'dd-list',
		itemClass : 'dd-item',
		dragClass : 'dd-dragel',
		handleClass : 'dd-handle',
		collapsedClass : 'dd-collapsed',
		placeClass : 'dd-placeholder',
		noDragClass : 'dd-nodrag',
		emptyClass : 'dd-empty',
		expandBtnHTML : '<button data-action="expand" type="button">Expand</button>',
		collapseBtnHTML : '<button data-action="collapse" type="button">Collapse</button>',
		acaoDivHTML : '<div class="acao"> </div>',
		inputHTML : '<input type="text" name="editText">',
		wwwRoot : '',
		group : 0,
		maxDepth : 1,
		threshold : 20,
		imagens : {
			up : {
				name : "up.png",
				src : "imagens/up.png",
				alt : "",
				id : "up",
				title : "Mover para cima",
				srclink : false
			},
			down : {
				name : "down.png",
				src : "imagens/down.png",
				alt : "",
				id : "down",
				title : "Mover para baixo",
				srclink : false
			},
			left : {
				name : "left.png",
				src : "imagens/left.png",
				alt : "",
				id : "left",
				title : "Mover para esquerda",
				srclink : false
			},
			right : {
				name : "right.png",
				src : "imagens/right.png",
				alt : "",
				id : "right",
				title : "Mover para direita",
				srclink : false
			},
			edit : {
				name : "edit.png",
				src : "imagens/edit.png",
				alt : "",
				id : "edit",
				title : "Editar",
				srclink : false
			},
			del : {
				name : "delete.png",
				src : "imagens/delete.png",
				alt : "",
				id : "delete",
				title : "Excluir",
				srclink : false
			},
			salve : {
				name : "salve.png",
				src : "imagens/salve.png",
				alt : "",
				id : "salve",
				title : "Salvar",
				srclink : false
			},
			cancel : {
				name : "cancel.png",
				src : "imagens/cancel.png",
				alt : "",
				id : "cancel",
				title : "Cancelar",
				srclink : false
			},
			show : {
				name : "show.png",
				src : "imagens/show.png",
				alt : "",
				id : "show",
				title : "Mostrar",
				srclink : false
			},
			hide : {
				name : "hide.png",
				src : "imagens/hide.png",
				alt : "",
				id : "hide",
				title : "Ocultar",
				srclink : false
			}

		}

	};

	function Plugin(element, options) {
		this.w = $(window);
		this.el = $(element);
		this.options = $.extend({}, defaults, options);
		this.acoes = this.loadImagens();
		this.init();

	}

	Plugin.prototype = {

		init : function() {
			var list = this;

			list.reset();

			list.el.data('nestable-group', this.options.group);

			list.placeEl = $('<div class="' + list.options.placeClass + '"/>');

			$.each(this.el.find(list.options.itemNodeName), function(k, el) {
				list.setParent($(el));
				list.addAcao($(el));
			});

			list.el.on('click', 'button',
					function(e) {
						if (list.dragEl || (!hasTouch && e.button !== 0)) {
							return;
						}
						var target = $(e.currentTarget), action = target
								.data('action'), item = target
								.parent(list.options.itemNodeName);
						if (action === 'collapse') {
							list.collapseItem(item);
						}
						if (action === 'expand') {
							list.expandItem(item);
						}
					});

			list.el.on('click', 'img',
					function(e) {

						var target = $(e.currentTarget), acao = target
								.attr('id'), item = $(this).parent('.acao')
								.parent(list.options.itemNodeName);

						switch (acao) {
						case 'up':
							list.up(item);
							break;
						case 'down':
							list.down(item);
							break;
						case 'left':
							list.left(item);
							break;
						case 'right':
							list.right(item);
							break;
						case 'delete':
							list.del(item);
							break;
						case 'edit':
							list.edit(item);
							break;
						case 'salve':
							list.salve(item);
							break;
						case 'cancel':
							list.cancel(item);
							break;
						case 'show':
							list.show(item);
							break;
						case 'hide':
							list.hide(item);
							break;
						}

						return item;
					});

			var onStartEvent = function(e) {
				var handle = $(e.target);
				if (!handle.hasClass(list.options.handleClass)) {
					if (handle.closest('.' + list.options.noDragClass).length) {
						return;
					}
					handle = handle.closest('.' + list.options.handleClass);
				}
				if (!handle.length || list.dragEl
						|| (!hasTouch && e.button !== 0)
						|| (hasTouch && e.touches.length !== 1)) {
					return;
				}
				e.preventDefault();
				list.dragStart(hasTouch ? e.touches[0] : e);
			};

			var onMoveEvent = function(e) {
				if (list.dragEl) {
					e.preventDefault();
					list.dragMove(hasTouch ? e.touches[0] : e);
				}
			};

			var onEndEvent = function(e) {
				if (list.dragEl) {
					e.preventDefault();
					list.dragStop(hasTouch ? e.touches[0] : e);
				}
			};

			if (hasTouch) {
				list.el[0].addEventListener(eStart, onStartEvent, false);
				window.addEventListener(eMove, onMoveEvent, false);
				window.addEventListener(eEnd, onEndEvent, false);
				window.addEventListener(eCancel, onEndEvent, false);
			} else {
				list.el.on(eStart, onStartEvent);
				list.w.on(eMove, onMoveEvent);
				list.w.on(eEnd, onEndEvent);
			}

		},

		loadImagens : function() {
			var acao = $(this.options.acaoDivHTML);
			var objImg, cfgImg, imagem, attr, outImg = '', undef;

			if (typeof (this.options.imagens) === "object") {
				for (imagem in this.options.imagens) {
					cfgImg = this.options.imagens[imagem];
					if (objImg = defaults.imagens[imagem]) {
						if (typeof (cfgImg) === "object") {
							for (attr in cfgImg) {
								objImg[attr] = cfgImg[attr];

								if (attr == 'src')
									objImg.srclink = true;
							}
						}
					} else {
						defaults.imagens[imagem] = cfgImg;
					}
				}
			}

			for (imagem in defaults.imagens) {
				objImg = defaults.imagens[imagem];

				if (objImg.hasOwnProperty('href'))
					outImg = '<a href="' + objImg.href + '" title="'
							+ objImg.titlelink + '"> <img ';
				else
					outImg = '<img ';

				for (attr in objImg) {

					if ((attr != "" || attr != null || attr.length > 0)
							&& (attr != "srclink" && attr != "name" && attr != "href")) {
						if (attr == 'src') {
							if (objImg.srclink)
								outImg += attr + '="' + objImg[attr] + '" ';
							else
								outImg += attr + '="' + this.options.wwwRoot
										+ '/' + objImg[attr] + '"';
						} else
							outImg += attr + '="' + objImg[attr] + '" ';
					}
				}

				if (objImg.hasOwnProperty('href'))
					outImg += '> </a>';
				else
					outImg += '>';

				acao.append($(outImg));
			}

			return acao;
		},

		serialize : function() {
			var data, depth = 0, list = this;
			var ordem = 0;
			step = function(level, depth) {
				var array = [], items = level
						.children(list.options.itemNodeName);
				items.each(function() {
					ordem++;
					var li = $(this);
					li.data('ordem', ordem);
					var item = $.extend({}, li.data()), sub = li
							.children(list.options.listNodeName);

					if (sub.length) {
						item.children = step(sub, depth + 1);
					}
					array.push(item);
				});
				return array;
			};
			data = step(list.el.find(list.options.listNodeName).first(), depth);
			return data;
		},

		serialise : function() {
			return this.serialize();
		},

		reset : function() {
			this.mouse = {
				offsetX : 0,
				offsetY : 0,
				startX : 0,
				startY : 0,
				lastX : 0,
				lastY : 0,
				nowX : 0,
				nowY : 0,
				distX : 0,
				distY : 0,
				dirAx : 0,
				dirX : 0,
				dirY : 0,
				lastDirX : 0,
				lastDirY : 0,
				distAxX : 0,
				distAxY : 0
			};
			this.moving = false;
			this.dragEl = null;
			this.dragRootEl = null;
			this.dragDepth = 0;
			this.hasNewRoot = false;
			this.pointEl = null;
		},

		addAcao : function(li) {
			var acao = this.acoes.clone();

			if (li.children('.acao').html())
				li.children('.acao').remove();

			acao.children().hide();

			var lists = li.children(this.options.listNodeName);

			// if (li.attr('id').indexOf("artigo") == -1) {
			// if (!li.attr('data-parent'))
			// acao.children('#right').show();
			// } else {
			// if (li.prev().index() >= 0)
			// acao.children('#right').show();
			// }

			// if (li.parents(this.options.listNodeName).index() > 0)
			// acao.children('#left').show();

			if (li.next().index() >= 0)
				acao.children('#down').show();

			if (li.index() != 0)
				acao.children('#up').show();

			if (acao.children('a').html()
					&& li.attr('id').indexOf("artigo") == -1) {
				var id = li.data('id');
				var href = acao.children('a').attr('href');
				href = href + id;
				acao.children('a').attr('href', href);
				acao.children('a').show();
			}

			acao.children('#edit').show();
			acao.children('#delete').show();

			// if (li.attr('data-show') == "0") {
			// acao.children('#show').show();
			// } else {
			// acao.children('#hide').show();
			// }

			li.prepend(acao);
		},

		up : function(li) {
			var opt = this.options;
			var prev = li.prev();
			var pos = li.attr('data-id'), posprev = prev.attr('data-id');

			if (posprev != null) {

				li.insertBefore(prev);

				this.addAcao(li);
				this.addAcao(prev);
			}

		},

		down : function(li) {
			var opt = this.options;
			var next = li.next();
			var pos = li.attr('data-id'), posnext = next.attr('data-id');

			if (posnext != null) {
				li.insertAfter(next);
				this.addAcao(li);
				this.addAcao(next);
			}

		},

		left : function(li) {
			var opt = this.options;
			var parent = li.parent(opt.listNodeName).parent(opt.itemNodeName);
			var posnext = parent.attr('data-id');

			li.removeAttr('data-parent');

			if (posnext) {
				li.insertAfter(parent);
				if (parent.find(opt.itemNodeName).length == 0) {
					this.unsetParent(parent);
					this.addAcao(parent);
				}
				this.addAcao(li);
			}

		},

		right : function(li) {
			var opt = this.options;
			var prev = li.prev(opt.itemNodeName);

			li.attr('data-parent', prev.attr('data-id'));

			if (prev.length) {
				var list = prev.find(opt.listNodeName).last();
				if (!list.length) {
					list = $('<' + opt.listNodeName + '/>').addClass(
							opt.listClass);
					list.append(li);
					prev.append(list);
					this.setParent(prev);
					this.addAcao(li);
					this.addAcao(prev);
				} else {
					list = prev.children(opt.listNodeName).last();
					list.append(li);
					this.addAcao(li);
				}
			}

		},

		edit : function(li) {

			var opt = this.options;
			var texto = li.children('.dd-handle').text();

			var first = texto.substr(0, 1);
			var last = texto.substr(texto.length - 1, texto.length);

			if (first == " " && last == " ")
				texto = texto.substr(1, texto.length - 1);

			var input = $(opt.inputHTML).val(texto);
			var acao = this.acoes.clone();
			var hidden = $('<div class="hidden"></div>').html(texto).hide();

			acao.children().hide();

			li.children('.dd-handle').removeClass('dd-handle').addClass(
					'dd-input').html(input);

			li.children('.acao').remove();

			acao.children('#cancel').show();
			acao.children('#salve').show();

			li.prepend(acao);
			li.prepend(hidden);

		},

		salve : function(li) {
			var opt = this.options;
			var texto = li.children('.dd-input').children('input').val();

			var last = texto.substr(texto.length - 1, texto.length);

			if (last == " ")
				texto = texto.substr(0, texto.length - 1);

			li.children('.dd-input').children('input').remove();
			li.children('.dd-input').removeClass('dd-input').addClass(
					'dd-handle');
			li.children('.dd-handle').html(texto);

			li.children('.hidden').remove();
			li.data('value', texto);

			this.addAcao(li);

			updateTag(li.attr('data-id'), texto);

		},

		cancel : function(li) {
			var texto = li.children('.hidden').text();

			li.children('.dd-input').children('input').remove();
			li.children('.hidden').remove();

			li.children('.dd-input').removeClass('dd-input').addClass(
					'dd-handle');
			li.children('.dd-handle').html(texto);

			li.attr('data-value', texto);

			this.addAcao(li);
		},

		del : function(li) {
			var opt = this.options;
			var parent = li.parent(opt.listNodeName).parent(opt.itemNodeName);

			if (confirm(" Deseja deletar o item selecionado?")) {
				li.remove();

				if (parent.find(opt.itemNodeName).length == 0)
					this.unsetParent(parent);
			}

			var num = $('.dd-list li').size()-1;
			$('.dd-list li:eq(' + num + ') div #down').css('display', 'none');
			$('.dd-list li:first div #up').css('display', 'none');

			deleteTag(li.attr('data-id'));

		},

		show : function(li) {
			li.attr('data-show', "1");
			this.addAcao(li);
		},

		hide : function(li) {
			li.attr('data-show', "0");
			this.addAcao(li);
		},

		expandItem : function(li) {
			li.removeClass(this.options.collapsedClass);
			li.children('[data-action="expand"]').hide();
			li.children('[data-action="collapse"]').show();
			li.children(this.options.listNodeName).show();
		},

		collapseItem : function(li) {
			var lists = li.children(this.options.listNodeName);
			if (lists.length) {
				li.addClass(this.options.collapsedClass);
				li.children('[data-action="collapse"]').hide();
				li.children('[data-action="expand"]').show();
				li.children(this.options.listNodeName).hide();
			}
		},

		expandAll : function() {
			var list = this;
			list.el.find(list.options.itemNodeName).each(function() {
				list.expandItem($(this));
			});
		},

		collapseAll : function() {
			var list = this;
			list.el.find(list.options.itemNodeName).each(function() {
				list.collapseItem($(this));
			});
		},

		setParent : function(li) {
			if (li.children(this.options.listNodeName).length) {
				li.prepend($(this.options.expandBtnHTML));
				li.prepend($(this.options.collapseBtnHTML));
			}
			li.children('[data-action="expand"]').hide();
		},

		unsetParent : function(li) {
			li.removeClass(this.options.collapsedClass);
			li.children('[data-action]').remove();
			li.children(this.options.listNodeName).remove();
		},

		dragStart : function(e) {
			var mouse = this.mouse, target = $(e.target), dragItem = target
					.closest(this.options.itemNodeName);

			this.options.prev = null;
			this.options.next = null;

			if (dragItem.prev().html())
				this.options.prev = dragItem.prev().attr('id');

			if (dragItem.next().html())
				this.options.next = dragItem.next().attr('id');

			this.placeEl.css('height', dragItem.height());

			mouse.offsetX = e.offsetX !== undefined ? e.offsetX : e.pageX
					- target.offset().left;
			mouse.offsetY = e.offsetY !== undefined ? e.offsetY : e.pageY
					- target.offset().top;
			mouse.startX = mouse.lastX = e.pageX;
			mouse.startY = mouse.lastY = e.pageY;

			this.dragRootEl = this.el;

			this.dragEl = $(document.createElement(this.options.listNodeName))
					.addClass(
							this.options.listClass + ' '
									+ this.options.dragClass);
			this.dragEl.css('width', dragItem.width());

			// fix for zepto.js
			// dragItem.after(this.placeEl).detach().appendTo(this.dragEl);
			dragItem.after(this.placeEl);
			dragItem[0].parentNode.removeChild(dragItem[0]);
			dragItem.appendTo(this.dragEl);

			$(document.body).append(this.dragEl);
			this.dragEl.css({
				'left' : e.pageX - mouse.offsetX,
				'top' : e.pageY - mouse.offsetY
			});
			// total depth of dragging item
			var i, depth, items = this.dragEl.find(this.options.itemNodeName);
			for (i = 0; i < items.length; i++) {
				depth = $(items[i]).parents(this.options.listNodeName).length;
				if (depth > this.dragDepth) {
					this.dragDepth = depth;
				}
			}

			if (prev.html())
				this.addAcao(prev);
			if (next.html())
				this.addAcao(next);

		},

		dragStop : function(e) {
			// fix for zepto.js
			// this.placeEl.replaceWith(this.dragEl.children(this.options.itemNodeName
			// + ':first').detach());
			var el = this.dragEl.children(this.options.itemNodeName).first();
			el[0].parentNode.removeChild(el[0]);
			this.placeEl.replaceWith(el);

			this.dragEl.remove();
			this.el.trigger('change');
			if (this.hasNewRoot) {
				this.dragRootEl.trigger('change');
			}

			this.addAcao(el);
			var prev = $('#' + this.options.prev), next = $('#'
					+ this.options.next);

			if (prev.html())
				this.addAcao(prev);
			if (next.html())
				this.addAcao(next);

			if (el.next().html())
				this.addAcao(el.next());
			if (el.prev().html())
				this.addAcao(el.prev());

			this.reset();

			var opt = this.options;
			var parent = el.parent(opt.listNodeName).parent(opt.itemNodeName);
			if (parent.attr('data-id')) {
				el.attr('data-parent', parent.attr('data-id'));
			} else {
				el.removeAttr('data-parent');
			}

		},

		dragMove : function(e) {
			var list, parent, prev, next, depth, opt = this.options, mouse = this.mouse;

			this.dragEl.css({
				'left' : e.pageX - mouse.offsetX,
				'top' : e.pageY - mouse.offsetY
			});

			// mouse position last events
			mouse.lastX = mouse.nowX;
			mouse.lastY = mouse.nowY;
			// mouse position this events
			mouse.nowX = e.pageX;
			mouse.nowY = e.pageY;
			// distance mouse moved between events
			mouse.distX = mouse.nowX - mouse.lastX;
			mouse.distY = mouse.nowY - mouse.lastY;
			// direction mouse was moving
			mouse.lastDirX = mouse.dirX;
			mouse.lastDirY = mouse.dirY;
			// direction mouse is now moving (on both axis)
			mouse.dirX = mouse.distX === 0 ? 0 : mouse.distX > 0 ? 1 : -1;
			mouse.dirY = mouse.distY === 0 ? 0 : mouse.distY > 0 ? 1 : -1;
			// axis mouse is now moving on
			var newAx = Math.abs(mouse.distX) > Math.abs(mouse.distY) ? 1 : 0;

			// do nothing on first move
			if (!mouse.moving) {
				mouse.dirAx = newAx;
				mouse.moving = true;
				return;
			}

			// calc distance moved on this axis (and direction)
			if (mouse.dirAx !== newAx) {
				mouse.distAxX = 0;
				mouse.distAxY = 0;
			} else {
				mouse.distAxX += Math.abs(mouse.distX);
				if (mouse.dirX !== 0 && mouse.dirX !== mouse.lastDirX) {
					mouse.distAxX = 0;
				}
				mouse.distAxY += Math.abs(mouse.distY);
				if (mouse.dirY !== 0 && mouse.dirY !== mouse.lastDirY) {
					mouse.distAxY = 0;
				}
			}
			mouse.dirAx = newAx;

			/**
			 * move horizontal
			 */
			if (mouse.dirAx && mouse.distAxX >= opt.threshold) {
				// reset move distance on x-axis for new phase
				mouse.distAxX = 0;
				prev = this.placeEl.prev(opt.itemNodeName);
				// increase horizontal level if previous sibling exists and is
				// not collapsed
				if (mouse.distX > 0 && prev.length
						&& !prev.hasClass(opt.collapsedClass)) {
					// cannot increase level when item above is collapsed
					list = prev.find(opt.listNodeName).last();
					// check if depth limit has reached
					depth = this.placeEl.parents(opt.listNodeName).length;
					// ******************************************************
					if (depth + this.dragDepth <= opt.maxDepth
							&& prev.attr('id').indexOf("artigo") == -1) {
						// create new sub-level if one doesn't exist
						if (!list.length) {
							list = $('<' + opt.listNodeName + '/>').addClass(
									opt.listClass);
							list.append(this.placeEl);
							prev.append(list);
							this.setParent(prev);
							// this.addAcao(prev);
						} else {
							// else append to next level up
							list = prev.children(opt.listNodeName).last();
							list.append(this.placeEl);
						}
					}
				}
				// decrease horizontal level
				if (mouse.distX < 0) {
					// we can't decrease a level if an item preceeds the current
					// one
					next = this.placeEl.next(opt.itemNodeName);
					// ********************************************************
					if (!next.length) {
						parent = this.placeEl.parent();
						if (!parent.children().length) {
							this.placeEl.closest(opt.itemNodeName).after(
									this.placeEl);
							this.unsetParent(parent.parent());
						}
					}
				}
			}

			var isEmpty = false;

			// find list item under cursor
			if (!hasPointerEvents) {
				this.dragEl[0].style.visibility = 'hidden';
			}
			this.pointEl = $(document
					.elementFromPoint(
							e.pageX - document.body.scrollLeft,
							e.pageY
									- (window.pageYOffset || document.documentElement.scrollTop)));
			if (!hasPointerEvents) {
				this.dragEl[0].style.visibility = 'visible';
			}
			if (this.pointEl.hasClass(opt.handleClass)) {
				this.pointEl = this.pointEl.parent(opt.itemNodeName);
			}
			if (this.pointEl.hasClass(opt.emptyClass)) {
				isEmpty = true;
			} else if (!this.pointEl.length
					|| !this.pointEl.hasClass(opt.itemClass)) {
				return;
			}

			// find parent list of item under cursor
			var pointElRoot = this.pointEl.closest('.' + opt.rootClass), isNewRoot = this.dragRootEl
					.data('nestable-id') !== pointElRoot.data('nestable-id');

			/**
			 * move vertical
			 */
			if (!mouse.dirAx || isNewRoot || isEmpty) {
				// check if groups match if dragging over new root
				if (isNewRoot
						&& opt.group !== pointElRoot.data('nestable-group')) {
					return;
				}
				// check depth limit
				depth = this.dragDepth - 1
						+ this.pointEl.parents(opt.listNodeName).length;
				if (depth > opt.maxDepth) {
					return;
				}
				var before = e.pageY < (this.pointEl.offset().top + this.pointEl
						.height() / 2);
				parent = this.placeEl.parent();
				// if empty create new list to replace empty placeholder
				// ***********************************************************
				if (isEmpty) {
					list = $(document.createElement(opt.listNodeName))
							.addClass(opt.listClass);
					list.append(this.placeEl);
					this.pointEl.replaceWith(list);
				} else if (before) {
					this.pointEl.before(this.placeEl);
				} else {
					this.pointEl.after(this.placeEl);
				}
				if (!parent.children().length) {
					this.unsetParent(parent.parent());
				}
				if (!this.dragRootEl.find(opt.itemNodeName).length) {
					this.dragRootEl.append('<div class="' + opt.emptyClass
							+ '"/>');
				}
				// parent root list has changed
				if (isNewRoot) {
					this.hasNewRoot = true;
					this.dragRootEl = pointElRoot;
				}
			}
		}

	};

	$.fn.nestable = function(params) {
		var lists = this, retval = this;

		var $this = $(this);
		var data = $this.data('nestable');

		lists.each(function() {

			var plugin = $(this).data("nestable");

			if (!plugin) {
				$(this).data("nestable", new Plugin(this, params));
				$(this).data("nestable-id", new Date().getTime());
			} else {
				if (typeof params === 'string'
						&& typeof plugin[params] === 'function') {
					retval = plugin[params]();
				}
			}

		});

		$.fn.nestable.addItem = function(value) {
			insertTag(value);

			var num = lists.find(".dd-item").size();
			
			$('.dd-list li:eq(' + (num-1) + ') div #down').css('display', 'inline');
			
			var li = $("<li> </li>");
			li.addClass("dd-item").attr("data-id", "null").attr("id",
					"item_" + num).attr("data-value", value);
			var div = $("<div> </div").addClass("dd-handle").text(value);
			var acao = data.acoes.clone();

			acao.children().hide();
			if (num > 0) {
				acao.children('#up').show();
			}
			acao.children('#edit').show();
			acao.children('#delete').show();
			li.append(acao);
			li.append(div);
			lists.children(".dd-list").first().append(li);

			return retval || lists;
		};

		return retval || lists;
	};

})(window.jQuery || window.Zepto, window, document);
