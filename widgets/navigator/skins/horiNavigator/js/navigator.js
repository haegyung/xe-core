function WidgetNavigator(menu_srl){
    var self = this;
    self.menu_srl = menu_srl;

    // 1depth 메뉴를 먼저
    jQuery(function(){
        jQuery('ul.widget_navigator_'+menu_srl+' > li')
            .mouseover(
                function(e){
                    jQuery(this).parent().children('li').removeClass('active');
                    jQuery(this).addClass('active');

                    var node_srl = jQuery(this).attr('node_srl');
                    if(self.menu_srl && node_srl && widget_navigator && widget_navigator[self.menu_srl]){

                        jQuery('ul[node_srl='+node_srl+']').remove();
                        var wn = widget_navigator[self.menu_srl].drawMenu(node_srl);
                        if(wn) wn.appendTo(jQuery('html>body'));

                    }
                }
            ).mouseout(
                function(e){
                    var node_srl = jQuery(this).attr('node_srl');

                    if(jQuery(e.relatedTarget).is("ul[node_srl='"+node_srl+"']") || jQuery(e.relatedTarget).parents("ul[node_srl='"+node_srl+"']").size()>0){
                        return false;
                    }
                    jQuery('ul[node_srl='+node_srl+']').hide();
                    jQuery(this).parent().children('li.active').removeClass('active');
                    jQuery(this).parent().children('li._active').addClass('active');

                }
        );

    });
}


WidgetNavigator.prototype.drawMenu = function(parent_srl){
    var self = this;
    var c = this.getMenu(parent_srl);

    // 하위메뉴가 없다
    if(c.size()==0) return '';

    var depth = this.getDepth(parent_srl);

    // 하위 메뉴를 만든다
    var h = jQuery('<ul class="widgetNavSub '+hrMenuColorset+'" node_srl="'+parent_srl+'">')

            .css({ position:'absolute' })
            .css({ zIndex:9999 })

            .mouseover(function(){

                jQuery('li.[node_srl='+parent_srl+']').parent().show();
                jQuery(this).show();

            })
            .mouseout(function(){
//                jQuery(this).hide();
            });

    // 1차메뉴
    if(depth <1){
        var parent_offset = jQuery('li.node_'+parent_srl).offset();
        h.css({
                top : parent_offset.top + jQuery('li.node_'+parent_srl).height()-2,
                left : parent_offset.left
             })

    // 2차메뉴
    }else{
        h.css({
                left: 40
             });
    }

    h.mouseout(function(e){
        var node_srl = jQuery(this).attr('node_srl');
        if(jQuery(e.relatedTarget).is("ul[node_srl='"+node_srl+"']") || jQuery(e.relatedTarget).parents("ul[node_srl='"+node_srl+"']").size()>0){
            return false;
        }else{
            jQuery(this).hide();
        }
    });


    c.each(function(i){
        var t = jQuery(this);

        var m = t.attr('text');
        if(m){
            var u ='#';
            if(t.attr('url')){
                if(/^http\:\/\//.test(t.attr('url'))){
                    u = t.attr('url');
                }else{
                    u = request_uri;
                    if(typeof(xeVid)!='undefined') u = u.setQuery('vid',xeVid);
                    u = u.setQuery('mid',t.attr('url'));
                }
            }
            m = '<a href="' + u + '"'+(t.attr('open_window')=='Y'?' target="blank"':'')+'>'+m+'</a>';


            jQuery('<li class="node_'+ t.attr('node_srl') +( i==0?'  ':'')+'" node_srl="'+ t.attr('node_srl')+'">')
                .html(m)
                .mouseover(function(){
                    jQuery(this).toggleClass('active','');
                    var node_srl = jQuery(this).attr('node_srl');

                    if(self.menu_srl && node_srl && widget_navigator && widget_navigator[self.menu_srl]){
                        if(jQuery('ul[node_srl='+node_srl+']').size() ==0){
                            var wn = widget_navigator[self.menu_srl].drawMenu(node_srl);
    //                        if(wn) wn.appendTo(jQuery('li[node_srl='+node_srl+']'));
                        }else{
                            jQuery('ul[node_srl='+node_srl+']').show();
                        }
                    }
                })
                .mouseout(function(){
                    var node_srl = jQuery(this).attr('node_srl');
                    jQuery('ul[node_srl='+node_srl+']').hide();
                })
                .appendTo(h);
        }
    });


    return h;
}


WidgetNavigator.prototype.load = function(xml){
    this.xml = xml;
    var self = this;
    jQuery.get(xml,{},function(data){
        // for ie
        self.data = jQuery(data.replace(/\<(\/|)node/g,'<$1span').replace(/\<(\/|)root/g,'<$1div'));
    },'text');
}

WidgetNavigator.prototype.getMenu = function(parent_srl){
    var m = this.data;
    return m.find("[parent_srl="+parent_srl+"]");
}

WidgetNavigator.prototype.getDepth = function(node_srl){
    var m = this.data.find('span[node_srl='+node_srl+']');
    return m.parents("span").size();
}
