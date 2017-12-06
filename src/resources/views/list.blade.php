{{-- styles --}}
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.6.1/css/bulma.min.css">
<link rel="stylesheet" href="{{ asset('assets/vendor/Odin/style.css') }}"/>

<section id="app" v-cloak>
    {{-- notifications --}}
    <div class="notif-container">
        @if (session('status'))
            <my-notification
                title="Success"
                body="{{ session('status') }}"
                type="success">
            </my-notification>
        @endif

        <my-notification></my-notification>
    </div>

    {{-- Odin --}}
    <odin-comp inline-template
        :odin-trans="{{ json_encode(['ajax_fail'=>trans('Odin::messages.ajax_fail')]) }}"
        :rev-list="{{ json_encode($revisions->pluck('id')) }}">

        <div>
            {{-- overlay --}}
            <div class="odin-animated fadeIn" :class="{'shade' : selected}" @click="toggleRev()"></div>

            <div class="columns">
                <div class="column is-10 revisions" ref="revisions">
                    {{-- list --}}
                    <table class="table is-hoverable is-narrow">
                        <tbody>
                            @foreach ($revisions as $rev)
                                @php
                                    $id = $rev->id;
                                    $user = $rev->user;
                                    $time = Carbon\Carbon::parse($rev->created_at);
                                @endphp

                                <tr class="revisions-link" v-multi-ref="'rev-{{ $id }}'" @click="toggleRev({{ $id }})">

                                    {{-- user avatar --}}
                                    @if (isset($user->avatar))
                                        <td class="has-text-center">
                                            <figure class="image is-24x24">
                                                    <img src="{{ $user->avatar }}">
                                            </figure>
                                        </td>
                                    @endif

                                    {{-- user name --}}
                                    @if (isset($user->name))
                                        <td>{{ $user->name }}</td>
                                    @endif

                                    <td>{{ $time->diffForHumans() }} <strong>"{{ $time->format('F j, Y @ h:i:s A') }}"</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- diff --}}
                    <div class="compare-page odin-animated fadeInUp" v-show="selected" ref="container">
                        {{-- close --}}
                        <div class="is-pulled-right">
                            <button class="delete" @click="toggleRev()"></button>
                        </div>

                        {{-- content --}}
                        <ul class="timeline">
                            @foreach ($revisions as $rev)
                                @php
                                    $id = $rev->id;
                                    $user = $rev->user;
                                    $time = Carbon\Carbon::parse($rev->created_at);
                                    $html = app('odin')->toHtml($rev);
                                    $class = $rev->event == 'created' ? 'is-link is-outlined' : 'is-warning';

                                    $previewCheck = isset($template) && !in_array($rev->event, ['deleted', 'created']);
                                @endphp

                                {{-- date --}}
                                <li id="{{ $id }}" class="timeline-header" v-multi-ref="'rev-{{ $id }}'">
                                    <button class="tag is-rounded is-medium is-black revisions-link"
                                        @click.stop="updateRev({{ $id }}), goTo('{{ $id }}')">
                                        {{ $time->diffForHumans() }}
                                    </button>
                                </li>

                                {{-- data --}}
                                <li class="timeline-item" v-multi-ref="'rev-{{ $id }}'">
                                    <div class="timeline-marker is-icon" :class="{'is-link' : selected == '{{ $id }}'}">
                                        <template v-if="selected == '{{ $id }}'">
                                            <i class="fa fa-flag"></i>
                                        </template>
                                    </div>

                                    <div class="timeline-content">
                                        <div class="heading">
                                            <p><span class="title">{{ $rev->event }}</span></p>
                                            <p><span class="subtitle is-6">{{ $time->format('F j, Y @ h:i:s A') }}</span></p>
                                            <p><small class="subtitle is-6">By</small> <span class="subtitle is-5">{{ $user->name }}</span></p>
                                        </div>

                                        <div>
                                            {{-- body --}}
                                            <section class="compare-page__body">
                                                @if ($html)
                                                    {!! $html !!}
                                                @else
                                                    <p class="title is-5 is-info is-marginless notification">
                                                        {{ trans('Odin::messages.no_diff') }}
                                                    </p>
                                                @endif
                                            </section>

                                            {{-- ops --}}
                                            @if (count($revisions) > 1)
                                                <div class="compare-page__footer">

                                                    @if ($rev->event == 'created')
                                                        <p class="title is-6">{{ trans('Odin::messages.reset_data') }}</p>
                                                    @endif

                                                    <div class="level">
                                                        @if ($html)
                                                            {{-- preview --}}
                                                            <div class="level-left">
                                                                @if ($previewCheck)
                                                                    <div class="level-item">
                                                                        {{ Form::open(['route' => ['odin.preview', $id], 'target'=>'_blank']) }}
                                                                            <input type="hidden" name="template" value="{{ $template }}">
                                                                            <button class="button is-link is-outlined">{{ trans('Odin::messages.preview') }}</button>
                                                                        {{ Form::close() }}
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="level-right">
                                                                <div class="level-item">
                                                                    @if ($rev->event == 'deleted')
                                                                        {{-- restore softDelete --}}
                                                                        {{ Form::open(['route' => ['odin.restore.soft', $id], 'method' => 'PUT']) }}
                                                                            <button class="button is-success">{{ trans('Odin::messages.res_model') }}</button>
                                                                        {{ Form::close() }}

                                                                    @else
                                                                        {{-- restore normal --}}
                                                                        {{ Form::open(['route' => ['odin.restore', $id]]) }}
                                                                            <button class="button {{ $class }}">{{ trans('Odin::messages.res') }}</button>
                                                                        {{ Form::close() }}

                                                                    @endif
                                                                </div>

                                                                {{-- remove revision --}}
                                                                <div class="level-item">
                                                                    {{ Form::open([
                                                                        'route' => ['odin.remove', $id],
                                                                        'method' => 'DELETE',
                                                                        'data-id' => $id,
                                                                        '@submit.prevent' => 'removeRev($event)'
                                                                    ]) }}
                                                                        <button class="button is-danger">{{ trans('Odin::messages.del') }}</button>
                                                                    {{ Form::close() }}
                                                                </div>
                                                            </div>

                                                        @else
                                                            <div class="level-left"></div>
                                                            <div class="level-right">
                                                                {{-- remove revision --}}
                                                                <div class="level-item">
                                                                    {{ Form::open([
                                                                        'route' => ['odin.remove', $id],
                                                                        'method' => 'DELETE',
                                                                        'data-id' => $id,
                                                                        '@submit.prevent' => 'removeRev($event)'
                                                                    ]) }}
                                                                        <button class="button is-danger">{{ trans('Odin::messages.del') }}</button>
                                                                    {{ Form::close() }}
                                                                </div>
                                                            </div>

                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </odin-comp>
</section>

{{-- Footer --}}
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="{{ asset('path/to/app.js') }}"></script>
